<?php

use Bitrix\Main\EventManager;

$eventManager = EventManager::getInstance();
// $eventManager->addEventHandler('module', 'event.name', [Callbacks::className(), 'method']); // example

/**
 * Не меняем установленные через сайт разделы товара при обмене с 1С
 */
$eventManager->addEventHandler("iblock", "OnBeforeIBlockElementUpdate", function (&$arFields) {
    $request = \Bitrix\Main\Context::getCurrent()->getRequest();
    if ($request->get("mode") == 'import') {
        // Получаем разделы товара
        \Bitrix\Main\Loader::includeModule("iblock");
        $resGroupElement = \CIBlockElement::GetElementGroups($arFields['ID'], true);
        while ($arGroupElement = $resGroupElement->Fetch()) {
            // Если данного раздела нет в импорте 1С, то добавляем его
            if (!in_array($arGroupElement['ID'], $arFields['IBLOCK_SECTION'])) {
                $arFields['IBLOCK_SECTION'][] = $arGroupElement['ID'];
            }
        }
    }
});

/**
 * При импорте из 1С не активируем разделы
 */
$eventManager->addEventHandler("iblock", "OnBeforeIBlockSectionUpdate", function (&$arFields) {
    $request = \Bitrix\Main\Context::getCurrent()->getRequest();
    if ($request->get("mode") == 'import' || $request->get("mode") == 'deactivate') {
        unset($arFields['ACTIVE']);
    }
});

/**
 * ИЗМЕНЕНИЕ РЕЗУЛЬТАТА КОНТРОЛЛЕРА КАТАЛОГА
 * Проставляем картинки из торговых предложений
 */
$eventManager->addEventHandler('main', 'Cube\Controllers\Shop\Catalog::onAfterAction', function (\Bitrix\Main\Event $event) {
    /** @var \Bitrix\Main\Engine\InlineAction $action */
    $action = $event->getParameter('action');
    /** @var \Bitrix\Main\Engine\InlineAction $action */
    $result = $event->getParameter('result');

    // Если это экшен listAction
    if ($action->getName() !== 'list') {
        return;
    }

    $params = $action->getBinder()->getMethodParams();
    if (!$params['iblockId']|| $params['iblockId'] !== \Cube\Controllers\Shop\Catalog::CATLOG_IBLOCK_ID) {
        return;
    }

    $productIds = array_unique(array_column($result['items'], 'ID'));

    if (!empty($productIds)) {
        /** @var \Bitrix\Main\ORM\Query\Result $siblingsColorsOffers */
        $withPhotoOffers = \Bitrix\Iblock\Elements\ElementOffersTable::query()
            ->whereIn('CML2_LINK.VALUE', $productIds)
            ->whereNotNull('MORE_PHOTO.VALUE')
            ->addGroup('CML2_LINK.VALUE')
            ->addSelect('ID')
            ->addSelect('CML2_LINK.VALUE', 'PARENT_ID')
            ->fetchAll();

        $withPhotoOffersIds = [];
        foreach ($withPhotoOffers as $withPhotoOffer) {
            if (!isset($withPhotoOffersIds[$withPhotoOffer['PARENT_ID']])) {
                $withPhotoOffersIds[$withPhotoOffer['PARENT_ID']] = $withPhotoOffer['ID'];
            }
        }
        if (!empty($withPhotoOffersIds)) {
            $withPhotoOffersIds = \Bitrix\Iblock\Elements\ElementOffersTable::query()
                ->whereIn('ID', $withPhotoOffersIds)
                ->addSelect('CML2_LINK.VALUE')
                ->addSelect('MORE_PHOTO.FILE')
                ->fetchCollection();

            $photos = [];
            $basePath = \Bitrix\Main\Engine\UrlManager::getInstance()->getHostUrl();
            foreach ($withPhotoOffersIds as $objectOfferPhoto) {
                $parentId = $objectOfferPhoto->get('CML2_LINK')->get('VALUE');
                $morePhotoProperty = $objectOfferPhoto?->get('MORE_PHOTO');
                if ($morePhotoProperty instanceof \Bitrix\Main\ORM\Objectify\Collection) {
                    $morePhotoProperty = array_map(function ($morePhotoProperty) use ($basePath) {
                        $fileValues = $morePhotoProperty->get('FILE')?->collectValues();
                        return $basePath . \CFile::GetFileSRC($fileValues);
                    }, $morePhotoProperty->getAll());
                } else {
                    $fileValues = $morePhotoProperty->get('FILE')?->collectValues();
                    $morePhotoProperty = $basePath . \CFile::GetFileSRC($fileValues);
                }
                $photos[$parentId] = $morePhotoProperty;
            }

            if (!empty($photos)) {
                foreach ($result['items'] as &$item) {
                    if (isset($photos[$item['ID']])) {
                        $item['PROPERTIES']['MORE_PHOTO'] = $photos[$item['ID']];
                    }
                }
                unset($item);

                // Отдаем резульат
                $event->setParameter('result', $result);
            }
        }
    }
});

/**
 * ИЗМЕНЕНИЕ РЕЗУЛЬТАТА КОНТРОЛЛЕРА ТОРГОВЫХ ПРЕДЛОЖЕНИЙ
 * Проставляем кратинки из соседних по цвету ТП и цвет из HL блока
 */
$eventManager->addEventHandler('main', 'Cube\Controllers\Shop\Catalog::onAfterAction', function (\Bitrix\Main\Event $event) {
    /** @var \Bitrix\Main\Engine\InlineAction $action */
    $action = $event->getParameter('action');
    /** @var \Bitrix\Main\Engine\InlineAction $action */
    $result = $event->getParameter('result');

    // Если это экшен listAction
    if ($action->getName() !== 'list') {
        return;
    }

    $params = $action->getBinder()->getMethodParams();
    if (!$params['iblockId']|| $params['iblockId'] !== \Cube\Controllers\Shop\Catalog::OFFERS_IBLOCK_ID) {
        return;
    }

    // Проставляем цвет из HL блока
    $result['items'] = array_map(function ($item) {
        if (!empty($item['PROPERTIES']['TSVET']['XML_ID'])) {
            $item['PROPERTIES']['TSVET'] = \Cube\ORM\PropertyTsvetTable::getColorByXmlId($item['PROPERTIES']['TSVET']['XML_ID']);
        }
        return $item;
    }, $result['items']);

    // Ищем без картинок товары
    $notMorePhotoItemsIdsList = [];
    foreach ($result['items'] as &$item) {
        if (!empty($item['PROPERTIES']['CML2_LINK'])
            && !empty($item['PROPERTIES']['TSVET']['XML_ID'])
            && isset($item['PROPERTIES']['MORE_PHOTO'])
            && empty($item['PROPERTIES']['MORE_PHOTO'])) {
            $notMorePhotoItemsIdsList[] = [
                'PARENT_ID'     => $item['PROPERTIES']['CML2_LINK'] ?? null,
                'ID'            => $item['ID'],
                'TSVET_XML_ID'  => $item['PROPERTIES']['TSVET']['XML_ID'],
                'MORE_PHOTO'    => &$item['PROPERTIES']['MORE_PHOTO']
            ];
        }
    }
    unset($item);

    $iblockObject = \Bitrix\Iblock\Iblock::wakeUp(\Cube\Controllers\Shop\Catalog::OFFERS_IBLOCK_ID);
    $iblockEntity = $iblockObject->getEntityDataClass();
    $siblingsColorsOffers = $iblockEntity::query()
        ->whereIn('CML2_LINK.VALUE', array_column($notMorePhotoItemsIdsList, 'PARENT_ID'))
        ->whereNotNull('MORE_PHOTO.VALUE')
        ->addSelect('CML2_LINK.VALUE')
        ->addSelect('MORE_PHOTO.FILE')
        ->addSelect('TSVET.ITEM.XML_ID')
        ->fetchCollection();

    $siblingResult = [];

    // Разбираем коллекцию или объект кратинок
    $basePath = \Bitrix\Main\Engine\UrlManager::getInstance()->getHostUrl();
    foreach ($siblingsColorsOffers as $itemSibling) {
        $parentId = $itemSibling->get('CML2_LINK')?->get('VALUE');
        $colorXmlId = $itemSibling->get('TSVET')?->get('ITEM')?->get('XML_ID');
        $morePhotoProperty = $itemSibling?->get('MORE_PHOTO');
        if ($morePhotoProperty instanceof \Bitrix\Main\ORM\Objectify\Collection) {
            $morePhotoProperty = array_map(function ($morePhotoProperty) use ($basePath) {
                $fileValues = $morePhotoProperty->get('FILE')?->collectValues();
                return $basePath . \CFile::GetFileSRC($fileValues);
            }, $morePhotoProperty->getAll());
        } else {
            $fileValues = $morePhotoProperty->get('FILE')?->collectValues();
            $morePhotoProperty = $basePath . \CFile::GetFileSRC($fileValues);
        }
        $siblingResult[$parentId . '_' . $colorXmlId] = $morePhotoProperty;
    }

    // Проставляем элементу картинки
    foreach ($notMorePhotoItemsIdsList as $notMorePhotoItem) {
        if (isset($siblingResult[$notMorePhotoItem['PARENT_ID'] . '_' . $notMorePhotoItem['TSVET_XML_ID']])) {
            $notMorePhotoItem['MORE_PHOTO'] = $siblingResult[$notMorePhotoItem['PARENT_ID'] . '_' . $notMorePhotoItem['TSVET_XML_ID']];
        }
    }

    // Отдаем резульат
    $event->setParameter('result', $result);
});

/**
 * ИЗМЕНЕНИЕ РЕЗУЛЬТАТА КОНТРОЛЛЕРА КОРЗИНЫ
 * Проставляем свойства, название из тп и товара
 */
$eventManager->addEventHandler('main', 'Cube\Controllers\Shop\Basket::onAfterAction', function (\Bitrix\Main\Event $event) {
    /** @var \Bitrix\Main\Engine\InlineAction $action */
    $action = $event->getParameter('action');
    /** @var \Bitrix\Main\Engine\InlineAction $action */
    $result = $event->getParameter('result');
    /** @var \Cube\Controllers\Shop\Basket $controller */
    $controller = $event->getParameter('controller');

    if ($action->getName() !== 'list') {
        return;
    }

    $iblockItems = [];
    foreach ($result['items'] as $item) {
        $iblockId = $item['IBLOCK_ID'];
        $iblockItems[$iblockId][] = $item['PRODUCT_ID'];
    }

    $catalogItemsData = [];
    foreach ($iblockItems as $iblockId => $productIds) {
        $catalogItemsResult = $controller->forward(\Cube\Controllers\Shop\Catalog::class, 'list', [
            'iblockId' => $iblockId,
            'filter' => [
                'ID' => $productIds
            ],
            'size' => 0
        ]);

        if ($catalogItemsResult && isset($catalogItemsResult['items'])) {
            $catalogItemsData = $catalogItemsData + array_combine(array_column($catalogItemsResult['items'], 'ID'), $catalogItemsResult['items']);
        }
    }

    $existParentItemsIds = array_values(array_unique(array_map(fn ($item) => $item['PROPERTIES']['CML2_LINK'], $catalogItemsData)));
    $productData = \Bitrix\Iblock\ElementTable::query()
        ->whereIn('ID', $existParentItemsIds)
        ->addSelect('ID')
        ->addSelect('NAME')
        ->addSelect('CODE')
        ->fetchAll();

    $productData = array_combine(array_column($productData, 'ID'), $productData);

    if ($catalogItemsData) {
        foreach ($result['items'] as &$item) {
            $productId = $item['PRODUCT_ID'];
            $itemData  = $catalogItemsData[$productId];
            $item['PROPERTIES'] = $itemData['PROPERTIES'] ?? null;

            if ($itemData && $parentId = $itemData['PROPERTIES']['CML2_LINK']) {
                $item['REAL_NAME'] = $item['NAME'];
                $item['NAME'] = $productData[$parentId]['NAME'];
                $item['CODE'] = $productData[$parentId]['CODE'];
                $item['PARENT_ID'] = $parentId;
            }
        }
        unset($item);
    }

    $event->setParameter('result', $result);
});
