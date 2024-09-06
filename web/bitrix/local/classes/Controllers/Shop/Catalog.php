<?php

declare(strict_types=1);

namespace Cube\Controllers\Shop;

use Cube\Controllers\BaseController;
use Bitrix\Iblock\Model\PropertyFeature;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Error;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\ORM\Objectify\Collection;
use OpenApi\Attributes as OA;

/**
 * Постарайтесь кастомную логику выносить в события
 * Данный контроллер универсальный за исключением использованных в нем констант
 */
#[OA\Tag('catalog')]
class Catalog extends BaseController
{
    public const DEFAULT_SIZE_PAGE = 30;

    public const CATLOG_IBLOCK_ID = 1;
    public const OFFERS_IBLOCK_ID = 5;

    /**
     * Список дефолтных полей элемента
     *
     * @return array
     */
    static function getElementEntityAllowedList(): array
    {
        return [
            'ID',
            'NAME',
            'CODE',
            'IBLOCK_SECTION_ID',
            'PREVIEW_PICTURE',
            'PREVIEW_TEXT',
        ];
    }

    /**
     * Получение списка элементов каталога
     *
     * @return array|null
     */
    #[OA\Get(path: '/api/v1/catalog', tags: ['catalog'])]
    #[OA\Get(path: '/api/v1/offers', tags: ['catalog'], description: 'Получение торговых предложений')]
    #[OA\QueryParameter(name: 'select[]', description: 'Перечисление только нужных полей (по умолчанию все)', required: false, schema: new OA\Schema(type: 'array'))]
    #[OA\QueryParameter(name: 'filter[]', description: 'Фильтр по полям', required: false, schema: new OA\Schema(properties: [
        new OA\Property(property: 'filter', type: 'array', items: new OA\Items(properties: [
            new OA\Property(property: '>ID', example: 1)
        ]))
    ]))]
    #[OA\QueryParameter(name: 'page', description: 'Страница пагинации', example: 1)]
    #[OA\QueryParameter(name: 'size', description: 'Кол-во элементов на страницу', example: self::DEFAULT_SIZE_PAGE)]
    #[OA\Response(response: 200, description: 'Список элементов каталога')]
    #[OA\Response(response: 400, description: 'Неверный запрос')]
    public function listAction(string|int $iblockId = self::CATLOG_IBLOCK_ID, array $select = [], array $filter = [], int $page = 1, int $size = self::DEFAULT_SIZE_PAGE, PageNavigation $pageNavigation = null): ?array
    {
        Loader::includeModule('iblock');
        Loader::includeModule('catalog');

        $result = [
            'items' => [],
            'page'  => $page,
            'size'  => $size,
            'count' => 0,
            'total' => 0
        ];

        // Получаем объект и далее сущность инфоблока
        $iblockObject = \Bitrix\Iblock\Iblock::wakeUp($iblockId);
        $iblockEntity = $iblockObject->getEntityDataClass();
        if (!$iblockEntity) {
            Context::getCurrent()->getResponse()->setStatus(500);
            $this->addError(new Error('iblock ' . $iblockId . ' not found'));
            return null;
        }

        // Получение свойств из механизма единого управления свойствами.
        // @see https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=42&LESSON_ID=1986
        $properties = empty($select) ? (PropertyFeature::getListPageShowPropertyCodes($iblockId, ['CODE' => 'Y']) ?? []) : [];

        // Pagintaion
        if ($pageNavigation && $pageNavigation instanceof PageNavigation) {
            $pageNavigation->setPageSize($size);
            $pageNavigation->setCurrentPage($page);
        }

        // prepare limit
        $qLimit = $pageNavigation ? $pageNavigation->getLimit() : $size;
        $qOffset = $pageNavigation ? $pageNavigation->getOffset() : 0;

        // Получаем объекты элемента
        $iblockCatalogQuery = $iblockEntity::query()
            ->addSelect('NAME')
            ->addSelect('CODE')
            ->setLimit($qLimit)
            ->setOffset($qOffset)
            ->where('ACTIVE', true);

        // Если свой select, разбираем на свойства и поля
        // Запрашиваем только поля для верной пагинации
        if ($select) {
            $properties = $select['PROPERTIES'] ?? [];
            unset($select['PROPERTIES']);
            $iblockCatalogQuery->setSelect(array_diff_key($select, array_flip($properties)));
        } else {
            $iblockCatalogQuery->setSelect($this->getElementEntityAllowedList());
        }

        if ($filter) {
            $iblockCatalogQuery->setFilter($filter);
        }

        $resultQuery = $iblockCatalogQuery->exec(); // Выполняем запрос
        $countTotal = $iblockCatalogQuery->queryCountTotal(); // Общее кол-во
        $elementCollection = $resultQuery->fetchCollection(); // Получаем коллекцию

        // Если коллекцию не получили
        if (!$elementCollection) {
            Context::getCurrent()->getResponse()->setStatus(500);
            $this->addError(new Error('Collection error'));
            return null;
        }

        // Узнаем тип и запрашиваем свойства
        $fillProperties = [];
        $propertyFieldsEntity = array_intersect_key((array) $elementCollection?->entity?->getFields(), array_flip($properties));
        /** @var \Bitrix\Iblock\ORM\Fields\PropertyReference $propertyField */
        foreach ($propertyFieldsEntity as $propertyField) {
            $iblockElementPropertyEntity = $propertyField->getIblockElementProperty();
            $valueEntity = $iblockElementPropertyEntity?->getValueEntity();
            $valueFields = $valueEntity?->getFields();

            // Поддержка типов
            if ($valueFields) {
                if (isset($valueFields['ITEM'])) {
                    $fillProperties[] = $propertyField->getName() . '.ITEM';
                } elseif (isset($valueFields['FILE'])) {
                    $fillProperties[] = $propertyField->getName() . '.FILE';
                } else {
                    $fillProperties[] = $propertyField->getName() . '.VALUE';
                }
            }
        }

        // Заполняем свойства
        if (!empty($fillProperties)) {
            $elementCollection->fill($fillProperties);
        }

        foreach ($elementCollection as $elementObject) {
            /** @var \Bitrix\Iblock\ORM\ValueStorageEntity */
            $item = [];
            $item = $this->convertedFields(array_diff_key($elementObject->collectValues(), array_flip($properties)));

            $propertiesValues = array_intersect_key($elementObject->collectValues(), array_flip($properties));
            $propertiesResult = $this->convertedProperties($propertiesValues);

            if (!empty($propertiesResult)) {
                $item['PROPERTIES'] = $propertiesResult;
            }

            $result['items'][] = $item;
        }

        $result['count'] = count($result['items']);
        $result['total'] = (int) $countTotal;

        return $result;
    }

    /**
     * Получение детального товара
     *
     * @return array|null
     */
    #[OA\Get(path: '/api/v1/catalog/product/{code}', tags: ['catalog'])]
    #[OA\PathParameter(name: 'code', example: 'test')]
    #[OA\Response(response: 200, description: 'Список элементов каталога')]
    #[OA\Response(response: 400, description: 'Неверный запрос')]
    #[OA\Response(response: 404, description: 'Страница не найдена')]
    public function getAction(string|int $iblockId = self::CATLOG_IBLOCK_ID, string|int $code): ?array
    {
        Loader::includeModule('iblock');
        Loader::includeModule('catalog');

        // Получение свойств из механизма единого управления свойствами.
        // @see https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=42&LESSON_ID=1986
        $properties = PropertyFeature::getDetailPageShowPropertyCodes($iblockId, ['CODE' => 'Y']) ?? [];

        $result = $this->listAction(
            iblockId: $iblockId,
            select: array_merge($this->getElementEntityAllowedList(), [
                'PROPERTIES' => $properties,
                'IBLOCK_SECTION_ID'
            ]),
            filter: [
                '=ACTIVE' => true,
                '=CODE'   => $code
            ]
        );

        // Детальные свойфства для детальной
        if (!empty($result['items'])) {
            $result = reset($result['items']);

            // Информация о разделе товара
            if ($result['IBLOCK_SECTION_ID']) {
                $result['SECTION'] = [];
                $sectionId = $result['IBLOCK_SECTION_ID'];

                // Запрашиваем данные о разделе
                $sectionResult = $this->sectionListAction(
                    iblockId: $iblockId,
                    filter: [
                        '=ACTIVE' => true,
                        '=ID'   => $sectionId
                    ],
                    select: [
                        'NAME',
                        'CODE'
                    ]
                );
        
                // Если не нашли
                if ($sectionResult && !empty($sectionResult['items'])) {
                    $result['SECTION'] = reset($sectionResult['items']);
                }
                unset($result['IBLOCK_SECTION_ID']);
            }

            // Получаем SEO параметры
            $ipropSectionValues = new \Bitrix\Iblock\InheritedProperty\ElementValues($iblockId, $result['ID']);
            $seoFields = $ipropSectionValues->getValues();

            $result['meta'] = [
                'metaTitle'       => $seoFields['ELEMENT_META_TITLE'] ?? null,
                'metaDescription' => $seoFields['ELEMENT_META_DESCRIPTION'] ?? null,
                'metaPageTitle'   => $seoFields['ELEMENT_PAGE_TITLE'] ?? null,
            ];
            return $result;
        }

        Context::getCurrent()->getResponse()->setStatus(404);
        $this->addError(new Error('Not found'));
        return null;
    }

    /**
     * Получение доступных разделов каталога
     *
     * @return array|null
     */
    #[OA\Get(path: '/api/v1/catalog/sections', tags: ['catalog'])]
    #[OA\QueryParameter(name: 'select[]', description: 'Перечисление только нужных полей (по умолчанию все)', required: false, schema: new OA\Schema(type: 'array'))]
    #[OA\QueryParameter(name: 'filter[]', description: 'Фильтр по полям', required: false, schema: new OA\Schema(properties: [
        new OA\Property(property: 'filter', type: 'array', items: new OA\Items(properties: [
            new OA\Property(property: 'ID', example: 1)
        ]))
    ]))]
    #[OA\QueryParameter(name: 'page', description: 'Страница пагинации', example: 1)]
    #[OA\QueryParameter(name: 'size', description: 'Кол-во элементов на страницу', example: self::DEFAULT_SIZE_PAGE)]
    #[OA\Response(response: 200, description: 'Список разделов каталога')]
    #[OA\Response(response: 400, description: 'Неверный запрос')]
    #[OA\Response(response: 404, description: 'Страница не найдена')]
    public function sectionListAction(string|int $iblockId = self::CATLOG_IBLOCK_ID, $select = [], array $filter = [], int $page = 1, int $size = self::DEFAULT_SIZE_PAGE, PageNavigation $pageNavigation = null): ?array
    {
        Loader::includeModule('iblock');
        Loader::includeModule('catalog');

        $result = [
            'items' => [],
            'page'  => $page,
            'size'  => $size,
            'count' => 0,
            'total' => 0
        ];

        $entitySection = \Bitrix\Iblock\Model\Section::compileEntityByIblock($iblockId);
        if (!$entitySection) {
            Context::getCurrent()->getResponse()->setStatus(500);
            $this->addError(new Error('Not found Iblock'));
            return null;
        }

        // Pagintaion
        if ($pageNavigation && $pageNavigation instanceof PageNavigation) {
            $pageNavigation->setPageSize($size);
            $pageNavigation->setCurrentPage($page);
        }

        // prepare limit
        $qLimit = $pageNavigation ? $pageNavigation->getLimit() : \Bitrix\Iblock\Controller\DefaultElement::DEFAULT_LIMIT;
        $qOffset = $pageNavigation ? $pageNavigation->getOffset() : 0;

        $sectionQuery = $entitySection::query()
            ->where('ACTIVE')
            ->addSelect('ID')
            ->addSelect('CODE')
            ->addSelect('NAME')
            ->addSelect('ACTIVE')
            ->addSelect('DESCRIPTION')
            ->addSelect('IBLOCK_ID')
            ->setLimit($qLimit)
            ->setOffset($qOffset);

        if ($select) {
            $sectionQuery->setSelect($select);
        }

        if ($filter) {
            $sectionQuery->setFilter($filter);
        }

        $resultSectionQuery = $sectionQuery->exec();

        // count total records
        $countTotal = $sectionQuery->queryCountTotal();
        $sectionCollection = $resultSectionQuery->fetchCollection();

        if ($sectionCollection) {
            foreach ($sectionCollection as $sectionObject) {
                $result['items'][] = $sectionObject->collectValues();
            }
        }

        $result['count'] = count($result['items']);
        $result['total'] = (int) $countTotal;

        return $result;
    }

   /**
     * Получение элементов и полей раздела
     *
     * @return array|null
     */
    #[OA\Get(path: '/api/v1/catalog/section/{code}', tags: ['catalog'])]
    #[OA\PathParameter(name: 'code', example: 'test')]
    #[OA\QueryParameter(name: 'select[]', description: 'Перечисление только нужных полей (по умолчанию все)', required: false, schema: new OA\Schema(type: 'array'))]
    #[OA\QueryParameter(name: 'filter[]', description: 'Фильтр по полям', required: false, schema: new OA\Schema(properties: [
        new OA\Property(property: 'filter', type: 'array', items: new OA\Items(properties: [
            new OA\Property(property: 'ID', example: 1)
        ]))
    ]))]
    #[OA\QueryParameter(name: 'page', description: 'Страница пагинации', example: 1)]
    #[OA\QueryParameter(name: 'size', description: 'Кол-во элементов на страницу', example: self::DEFAULT_SIZE_PAGE)]
    #[OA\Response(response: 200, description: 'Список элементов каталога и инфо о разделе')]
    #[OA\Response(response: 400, description: 'Неверный запрос')]
    #[OA\Response(response: 404, description: 'Страница не найдена')]
    public function sectionItemsAction(string|int $iblockId = self::CATLOG_IBLOCK_ID, string|int $code, array $select = [], array $filter = [], int $page = 1, int $size = self::DEFAULT_SIZE_PAGE, PageNavigation $pageNavigation = null): ?array
    {
        $sectionResult = $this->sectionListAction(
            iblockId: $iblockId,
            filter: [
                '=ACTIVE' => true,
                '=CODE'   => $code
            ],
        );

        // Если не нашли
        if (!$sectionResult || empty($sectionResult['items'])) {
            Context::getCurrent()->getResponse()->setStatus(404);
            $this->addError(new Error('Not found'));
            return null;
        }

        $section = reset($sectionResult['items']);
        // Получаем SEO параметры
        if ($section['IBLOCK_ID']) {
            $ipropSectionValues = new \Bitrix\Iblock\InheritedProperty\SectionValues($section['IBLOCK_ID'], $section['ID']);
            $seoFields = $ipropSectionValues->getValues();
        }

        $section['meta'] = [
            'metaTitle'       => $seoFields['SECTION_META_TITLE'] ?? null,
            'metaDescription' => $seoFields['SECTION_META_DESCRIPTION'] ?? null,
            'metaPageTitle'   => $seoFields['SECTION_PAGE_TITLE'] ?? null,
        ];

        // Pagintaion
        if ($pageNavigation && $pageNavigation instanceof PageNavigation) {
            $pageNavigation->setPageSize($size);
            $pageNavigation->setCurrentPage($page);
        }

        // Отдаем запрос контроллеру листинга товаров
        $result = $this->listAction(
            iblockId: $iblockId,
            select: $select,
            filter: array_merge([
                'SECTIONS.ID' => $section['ID']
            ], $filter),
            page: $page,
            size: $size,
            pageNavigation: $pageNavigation
        );

        // Добавляем в массив результата раздел
        $result['section'] = $section;
        return $result;
    }

    /**
     * Конвертация типов полей
     *
     * @param array $fields
     * @return void
     */
    private function convertedFields(array $fields)
    {
        $basePath = \Bitrix\Main\Engine\UrlManager::getInstance()->getHostUrl();
        foreach ($fields as $key => $value) {
            if ($key == 'PREVIEW_PICTURE') {
                $fields[$key] = $value ? $basePath . \CFile::GetPath($value) : null;
                continue;
            }
        }

        return $fields;
    }

    /**
     * Конвертация типов свойств
     *
     * @param array $propertiesCollection
     * @return void
     */
    private function convertedProperties(array $propertiesCollection)
    {
        $basePath = \Bitrix\Main\Engine\UrlManager::getInstance()->getHostUrl();
        foreach ($propertiesCollection as &$property) {
            $propertyEntity = $property?->entity;
            if ($propertyEntity?->hasField('ITEM')) {
                if ($property instanceof Collection) {
                    $property = array_map(function ($propertyObject) {
                        return $propertyObject->get('ITEM')?->collectValues();
                    }, $property->getAll());
                } else {
                    $property = $property->get('ITEM')?->collectValues();
                }
            // Файл, как правило изображения
            } elseif ($propertyEntity?->hasField('FILE')) {
                if ($property instanceof Collection) {
                    $property = array_map(function ($propertyObject) use ($basePath) {
                        $fileValues = $propertyObject->get('FILE')?->collectValues();
                        return $basePath . \CFile::GetFileSRC($fileValues);
                    }, $property->getAll());
                } else {
                    $fileValues = $property->get('FILE')?->collectValues();
                    $property = $basePath . \CFile::GetFileSRC($fileValues);
                }
            } else {
                // Обычное скалярное значение
                if ($property instanceof Collection) {
                    $property = array_map(function ($propertyObject) use ($basePath) {
                        return $propertyObject?->get('VALUE');
                    }, $property->getAll());
                } else {
                    $property = $property?->get('VALUE');
                }
            }
        }
        unset($property);
        return $propertiesCollection;
    }
}
