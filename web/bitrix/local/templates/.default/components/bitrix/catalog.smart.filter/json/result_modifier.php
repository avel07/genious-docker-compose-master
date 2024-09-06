<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
    die();
}

// Подтягиваем еще ID товаров сразу в результат
$FILTER_NAME = (string) $arParams["FILTER_NAME"];
$PREFILTER_NAME = (string) $arParams["PREFILTER_NAME"];

$arResult['ELEMENTS'] = [];

global ${$PREFILTER_NAME};
global ${$FILTER_NAME};
$preFilter = ${$PREFILTER_NAME};
if (!is_array($preFilter)) {
    $preFilter = [];
}

if (!empty(${$FILTER_NAME})) {
    $arFilter = $this->getComponent()->makeFilter($FILTER_NAME);
    if (!empty($preFilter)) {
        $arFilter = array_merge($preFilter, $arFilter);
    }
    if (\Bitrix\Main\Loader::includeModule('catalog')) {
        $arFilter = \CProductQueryBuilder::convertOldFilter($arFilter);
    }

    // Фильтр в компоненте под старое ядро. Да и тут ID свойств, так что, все быстро)
    $res = \CIBlockElement::GetList([], $arFilter, false, false, ['ID']);
    while ($item = $res->fetch()) {
        $arResult['ELEMENTS'][] = $item['ID'];
    }
}

// Для цвета добавляем данные из HL Блока
foreach ($arResult['ITEMS'] as &$property) {
    if ($property['CODE'] != 'TSVET') {
        continue;
    }
    foreach ($property['VALUES'] as &$value) {
        $value['DATA'] = $item['PROPERTIES']['TSVET'] = \Cube\ORM\PropertyTsvetTable::getColorByXmlId($value['URL_ID']);
    }
}

// Убираем лишние поля с JSON
unset(
    $arResult["COMBO"],
    $arResult["JS_FILTER_PARAMS"],
    $arResult["FILTER_URL"],
    $arResult["FILTER_AJAX_URL"],
    $arResult["FORM_ACTION"],
    $arResult["FACET_FILTER"],
    $arResult["SKU_PROPERTY_ID_LIST"],
    $arResult["PROPERTY_ID_LIST"],
    $arResult["PROPERTY_COUNT"],
    $arResult["SKU_PROPERTY_COUNT"],
);

// Убираем лишние поля с JSON
foreach ($arResult['ITEMS'] as &$item) {
    $item['VALUES'] = array_map(function ($values) {
        unset(
            // $values["CONTROL_ID"],
            $values["CONTROL_NAME"],
            $values["CONTROL_NAME_ALT"],
            $values["HTML_VALUE_ALT"],
            $values["HTML_VALUE"],
            $values["URL_ID"],
            $values["UPPER"]
        );
        return $values;
    }, $item['VALUES']);
}
unset($item);
