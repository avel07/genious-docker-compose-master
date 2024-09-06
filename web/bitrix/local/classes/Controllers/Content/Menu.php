<?php

declare(strict_types=1);

namespace Cube\Controllers\Content;

use Cube\Controllers\BaseController;
use Bitrix\Main\Context;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use OpenApi\Attributes as OA;
use Cube\Controllers\Shop\Catalog;

/**
 * Кастомное меню для логикик genious
 */
#[OA\Tag('menu')]
class Menu extends BaseController
{
    /**
     * Получить все меню | Лучший вариант | Кешируется
     *
     * @return array|null
     */
    #[OA\Get(path: '/api/v1/menu', tags: ['menu'])]
    #[OA\Response(response: 200, description: 'Структура всех меню')]
    #[OA\Response(response: 400, description: 'Неверный запрос')]
    public function allAction()
    {
        // cache
        $cache = \Bitrix\Main\Application::getInstance()->getCache();
        $cacheId = 'api_menu_all';       // Уникальный идентификатор
        $cachePath = '/rest/api_menu_all'; // Путь к кешу
        $cacheTtl = 3600;

        if ($cache->initCache($cacheTtl, $cacheId, $cachePath)) {
            $result = $cache->getVars();
        } else {
            Loader::includeModule('iblock');

            // Получаем идентификатор Iblock из ORM элементов инфоблока (без запроса)
            $queryObject = \Bitrix\Iblock\Elements\ElementMenuTable::query();
            $iblockId = $queryObject->getEntity()->getIblock()->get('ID');

            // Получаем все разделы первого уровня из инфоблока меню
            $parentSections = \Bitrix\Iblock\SectionTable::query()
                ->where('IBLOCK_ID', $iblockId)
                ->where('DEPTH_LEVEL', 1) // Только вложенность первого уровня
                ->addSelect('DEPTH_LEVEL')
                ->addSelect('CODE')
                ->fetchCollection();

            $result = [];
            foreach ($parentSections as $sectionObject) {
                $result[$sectionObject->get('CODE')] =  $this->forward($this, 'list', ['code' => $sectionObject->get('CODE')]);
            }

            $result['sections'] = $this->forward($this, 'sectionsList');

            // Начинаем писать в кеш
            if ($cache->startDataCache()) {
                // Тегированный кеш (обновляется при обновлении элемента в бд)
                $taggedCache = \Bitrix\Main\Application::getInstance()->getTaggedCache();
                $taggedCache->startTagCache($cachePath);                                // Путь к основному кешу (relativePath)
                $taggedCache->registerTag('iblock_id_' . $iblockId);                    // Тег инфоблока (триггер)
                $taggedCache->registerTag('iblock_id_' . Catalog::CATLOG_IBLOCK_ID);    // Тег инфоблока (триггер)
                $taggedCache->endTagCache();
                // Записываем в кеш результат
                $cache->endDataCache($result);
            }
        }
        return $result;
    }

    /**
     * Получить меню по символьному коду (разделу инфоблока 1 уровня)
     *
     * @return array|null
     */
    #[OA\Get(path: '/api/v1/menu/{code}', tags: ['menu'])]
    #[OA\Response(response: 200, description: 'Структура меню')]
    #[OA\Response(response: 404, description: 'Не найдено')]
    #[OA\Response(response: 400, description: 'Неверный запрос')]
    public function listAction(string $code): ?array
    {
        Loader::includeModule('iblock');

        $queryObject = \Bitrix\Iblock\Elements\ElementMenuTable::query();
        $iblockId = $queryObject->getEntity()->getIblock()->get('ID');

        $fieldsCode = [
            'NAME',
            'IBLOCK_SECTION_ID',
            'URL' => 'CODE'
        ];

        // Получаем главный раздел
        $parentSection = \Bitrix\Iblock\SectionTable::query()
            ->where('IBLOCK_ID', $iblockId)
            ->where('CODE', $code)
            ->setSelect($fieldsCode)
            ->addSelect('LEFT_MARGIN')
            ->addSelect('RIGHT_MARGIN')
            ->fetchObject();

        if (!$parentSection) {
            Context::getCurrent()->getResponse()->setStatus(404);
            $this->addError(new Error('Not found'));
            return null;
        }

        // Получаем все подразделы
        $sectionsCollection = \Bitrix\Iblock\SectionTable::query()
            ->where('IBLOCK_ID', $iblockId)
            ->where('LEFT_MARGIN', '>', $parentSection->get('LEFT_MARGIN'))
            ->where('RIGHT_MARGIN', '<', $parentSection->get('RIGHT_MARGIN'))
            ->setSelect($fieldsCode)
            ->fetchCollection();

        if ($sectionsCollection && $sectionsCollection->count()) {
            // Список всех ID разделов включая главный (Для выборки элементов (пунктов))
            $sectionsCollectionIds = array_merge([$parentSection->get('ID')], $sectionsCollection->getIdList());
        } else {
            $sectionsCollectionIds = [$parentSection->get('ID')];
        }

        // Получаем пункты меню
        $items = $queryObject
            ->addOrder('SORT', 'ASC')
            ->addOrder('ID', 'ASC')
            ->whereIn('SECTIONS.ID', $sectionsCollectionIds)
            ->setSelect($fieldsCode)
            ->fetchAll();

        // Собираем элементы по разделам
        $itemsSectionList = [];
        foreach ($items as $item) {
            $sectionId = $item['IBLOCK_SECTION_ID'];
            $itemsSectionList[$sectionId][] = $item;
        }

        $parentSectionData = $parentSection->collectValues();
        unset($parentSectionData['LEFT_MARGIN'], $parentSectionData['RIGHT_MARGIN']);
        $parentSectionData['ITEMS'] = isset($itemsSectionList[$parentSectionData['ID']]) ? $itemsSectionList[$parentSectionData['ID']] : null;

        if ($sectionsCollection && $sectionsCollection->count()) {
            // Собираем древо
            $sectionsList = [];
            foreach ($sectionsCollection as $sectionObject) {
                $section = $sectionObject->collectValues();
                $section['ITEMS'] = isset($itemsSectionList[$section['ID']]) ? $itemsSectionList[$section['ID']] : null;

                $sectionsList[] = $section;
            }

            // Собираем дерево. В качестве первого аргумента передаем массив с ID главного раздела
            $sectionsTree = $this->tree($parentSectionData, $sectionsList);

            $result = [
                'SECTIONS' => $sectionsTree['SECTIONS'],
                'ITEMS'    => $sectionsTree['ITEMS'],
            ];
        } else {
            $result = [
                'SECTIONS' => $parentSectionData['SECTIONS'],
                'ITEMS'    => $parentSectionData['ITEMS'],
            ];
        }
        return $result;
    }

    /**
     * Получить меню разделов каталога
     *
     * @return array|null
     */
    #[OA\Get(path: '/api/v1/menu/sections', tags: ['menu'])]
    #[OA\Response(response: 200, description: 'Структура меню')]
    #[OA\Response(response: 400, description: 'Неверный запрос')]
    public function sectionsListAction()
    {
        // Получаем меню каталога
        global $APPLICATION;
        $catalogMenuExt = $APPLICATION->IncludeComponent(
            "bitrix:menu.sections",
            "",
            array(
                "IS_SEF"           => "Y",
                "SEF_BASE_URL"     => "/catalog/",
                "SECTION_PAGE_URL" => "#SECTION_CODE#",
                "IBLOCK_TYPE"      => "catalog",
                "IBLOCK_ID"        => \Cube\Controllers\Shop\Catalog::CATLOG_IBLOCK_ID,
                "DEPTH_LEVEL"      => "3",
                "CACHE_TYPE"       => "N",
            ),
            false
        );

        $parentKey = false;
        $catalogMenu = [];
        foreach ($catalogMenuExt as $key => $data) {
            $item = [
                'NAME' => $data[0],
                'URL'  => $data[1],
            ];
            $item = array_merge($item, $data[3]);
            if ($item['DEPTH_LEVEL'] == 1) {
                if ($item['IS_PARENT']) {
                    $parentKey = $key;
                } else {
                    $parentKey = false;
                }

                $catalogMenu[$key] = $item;
            } else {
                if ($parentKey) {
                    $catalogMenu[$parentKey]['CHILD'][] = $item;
                    if ($item['SELECTED']) {
                        $catalogMenu[$parentKey]['SELECTED'] = true;
                    }
                }
            }
        }

        $result = [
            'ITEMS' => array_values($catalogMenu)
        ];

        return $result;
    }

    /**
     * Построитель дерева
     *
     * @param [type] $parent
     * @param [type] $nodes
     * @return array
     */
    private function tree($parent, $nodes): array
    {
        $parentNodes = array_values(array_filter($nodes, function ($node) use ($parent) {
            return $node['IBLOCK_SECTION_ID'] == $parent['ID'];
        }));

        foreach ($parentNodes as &$child) {
            $child = $this->tree($child, $nodes);
        }

        $parent['SECTIONS'] = $parentNodes;
        return $parent;
    }
}
