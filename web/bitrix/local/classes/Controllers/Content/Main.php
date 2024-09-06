<?php

declare(strict_types=1);

namespace Cube\Controllers\Content;

use Cube\Controllers\BaseController;
use Bitrix\Iblock\Model\PropertyFeature;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Error;
use Cube\Controllers\Shop\Catalog;
use OpenApi\Attributes as OA;

#[OA\Tag('main')]
class Main extends BaseController
{
    /**
     * Получение главной страницы
     *
     * @return array|null
     */
    #[OA\Get(path: '/api/v1/main', tags: ['main'])]
    #[OA\Response(response: 200, description: 'Главная страница Genious')]
    #[OA\Response(response: 400, description: 'Неверный запрос')]
    public function getAction(): ?array
    {
        $result = [];
        $basePath = \Bitrix\Main\Engine\UrlManager::getInstance()->getHostUrl();

        if (Loader::includeModule('iblock')) {
            // cache
            $cache = \Bitrix\Main\Application::getInstance()->getCache();
            $cacheId = 'api_main_page_main';       // Уникальный идентификатор
            $cachePath = '/rest/api_main_page'; // Путь к кешу
            $cacheTtl = 3600;

            if ($cache->initCache($cacheTtl, $cacheId, $cachePath)) {
                $result = $cache->getVars(); // достаем переменные из кеша
            } else {
                // Получаем объекты элемента
                $itemObject = \Bitrix\Iblock\Elements\ElementMainTable::query()
                    ->where('ACTIVE', true)
                    ->addSelect('ID')
                    ->addSelect('IBLOCK.ID')
                    ->addSelect('PREVIEW_PICTURE')
                    ->addSelect('DETAIL_PICTURE')
                    ->addSelect('blocks_1')
                    ->addSelect('text')
                    ->addSelect('image.DESCRIPTION')
                    ->addSelect('image.FILE')
                    ->addSelect('products')
                    ->addSelect('video')
                    ->addSelect('blocks_2')
                    ->addSelect('gallery')
                    ->fetchObject();

                if (!$itemObject) {
                    Context::getCurrent()->getResponse()->setStatus(404);
                    $this->addError(new Error('Страница не найдена', 'NOT_FOUND'));
                    return null;
                }
                
                // Получаем главный баннер
                $result['bannerMobile'] = !empty($itemObject->get('PREVIEW_PICTURE')) ? $basePath . \CFile::GetPath($itemObject->get('PREVIEW_PICTURE')) : null;
                $result['banner'] = !empty($itemObject->get('DETAIL_PICTURE')) ? $basePath . \CFile::GetPath($itemObject->get('DETAIL_PICTURE')) : null;

                // Получаем видео
                $result['video'] = !empty($itemObject->get('video')) ? $basePath . \CFile::GetPath($itemObject->get('video')->get('VALUE')) : null;

                // Получаем блоки-баннеры
                $result['blocks'] = [];
                $blocksTop = [];
                $blocksBottom = [];
                // Заполняем верхние и нижние блоки
                if ($itemObject->get('blocks_1')) {
                    foreach ($itemObject->get('blocks_1') as $blockObject) {
                        $result['blocks'][] = $blockObject->get('VALUE');
                        $blocksTop[$blockObject->get('VALUE')] = [];
                    }
                }
                if ($itemObject->get('blocks_2')) {
                    foreach ($itemObject->get('blocks_2') as $blockObject) {
                        $result['blocks'][] = $blockObject->get('VALUE');
                        $blocksBottom[$blockObject->get('VALUE')] = [];
                    }
                }
                if (!empty($result['blocks'])) {
                    $blocksCollection = \Bitrix\Iblock\Elements\ElementMainblocksTable::query()
                        ->where('ACTIVE', true)
                        ->setFilter(['ID' => $result['blocks']])
                        ->addSelect('ID')
                        ->addSelect('NAME')
                        ->addSelect('PREVIEW_PICTURE')
                        ->addSelect('link');
                    $blocksCollection->exec();
                    $blocksCollection = $blocksCollection->fetchCollection();

                    if (!empty($blocksCollection)) {
                        $result['blocks'] = [];
                        foreach ($blocksCollection as $blockObject) {
                            /** @var \Bitrix\Iblock\ORM\ValueStorageEntity */
                            $item = [];
                            $item['name'] = $blockObject->get('NAME');
                            $item['link'] = $blockObject->get('link')->get('VALUE');
                            $item['image'] = $basePath . \CFile::GetPath($blockObject->get('PREVIEW_PICTURE'));

                            if (key_exists($blockObject->get('ID'), $blocksTop)) {
                                $result['blocks']['top'][] = $item;
                            } else if (key_exists($blockObject->get('ID'), $blocksBottom)) {
                                $result['blocks']['bottom'][] = $item;
                            }
                        }
                    }
                }

                // Получение списка товаров через контроллер каталога
                $result['products'] = [];
                if ($itemObject->get('products')) {
                    foreach ($itemObject->get('products') as $productObject) {
                        $result['products'][] = $productObject->get('VALUE');
                    }
                }
                if (!empty($result['products'])) {
                    $catalog = new Catalog();
                    // Получаем свойства, которые выводятся в списках товаров
                    $properties = PropertyFeature::getListPageShowPropertyCodes(Catalog::CATLOG_IBLOCK_ID, ['CODE' => 'Y']);
                    $select = array_merge($catalog->getElementEntityAllowedList(), [
                        'PROPERTIES' => $properties,
                    ]);
                    // Делаем выборку из метода контроллера
                    $result['products'] = $catalog->listAction(Catalog::CATLOG_IBLOCK_ID, $select, ['ID' => $result['products']]);
                }

                // Получаем тексты
                $result['text'] = [];
                if ($itemObject->get('text')) {
                    foreach ($itemObject->get('text') as $textObject) {
                        $code = !empty($textObject->get('DESCRIPTION')) ? $textObject->get('DESCRIPTION') : null;
                        $result['text'][$code] = $textObject->get('VALUE');
                    }
                }

                // Получаем изображения
                $result['image'] = [];
                if ($itemObject->get('image')) {
                    foreach ($itemObject->get('image') as $imageObject) {
                        $imageFileObject = $imageObject->get('FILE');
                        $code = !empty($imageObject->get('DESCRIPTION')) ? $imageObject->get('DESCRIPTION') : null;
                        $result['image'][$code] = [
                            'height' => $imageFileObject->get('HEIGHT'),
                            'width'  => $imageFileObject->get('WIDTH'),
                            'type'   => $imageFileObject->get('CONTENT_TYPE'),
                            'src'    => $basePath . \CFile::GetFileSRC($imageFileObject->collectValues())
                        ];
                    }
                }

                // Получаем галерею
                $result['gallery'] = [];
                if ($itemObject->get('gallery')) {
                    foreach ($itemObject->get('gallery') as $imageObject) {
                        $result['gallery'][] = $basePath . \CFile::GetPath($imageObject->get('VALUE'));
                    }
                }

                // Получаем SEO параметры
                if ($itemObject->get('IBLOCK') && $itemObject->get('IBLOCK')->get('ID')) {
                    $ipropElementValues = new \Bitrix\Iblock\InheritedProperty\ElementValues($itemObject->get('IBLOCK')->get('ID'), $itemObject->get('ID'));
                    $seoFields = $ipropElementValues->getValues();
                }

                $result['meta'] = [
                    'metaTitle'       => $seoFields['ELEMENT_META_TITLE'] ?? null,
                    'metaDescription' => $seoFields['ELEMENT_META_DESCRIPTION'] ?? null,
                ];

                // Начинаем писать в кеш
                if ($cache->startDataCache()) {
                    // Тегированный кеш (обновляется при обновлении элемента в бд)
                    $taggedCache = \Bitrix\Main\Application::getInstance()->getTaggedCache();
                    $taggedCache->startTagCache($cachePath);                                            // Путь к основному кешу (relativePath)
                    $taggedCache->registerTag('iblock_id_' . $itemObject->get('IBLOCK')->get('ID'));    // Тег инфоблока (триггер)
                    $taggedCache->registerTag('iblock_id_' . Catalog::CATLOG_IBLOCK_ID);                // Тег инфоблока (триггер)
                    $taggedCache->endTagCache();
                    // Записываем в кеш результат
                    $cache->endDataCache($result);
                }
            }

            return $result;
        }
        return [];
    }
}
