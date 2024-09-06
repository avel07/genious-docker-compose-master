<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
    die();
}

// Component return $arResult

// =========== EXAMPLE =========== //
// ```
// Loader::includeModule("iblock");
// // Всегда режим фильтра
// $_GET['set_filter'] = 'Y';
// /** @var \CMain $APPLICATION */
// global $APPLICATION;
// global $smartFilter;
// $result = (array) $APPLICATION->IncludeComponent(
//     "bitrix:catalog.smart.filter",
//     "json",
//     [
//         "IBLOCK_ID"             => 1,
//         "SECTION_ID"            => 0,
//         "SECTION_CODE"          => null,
//         "SHOW_ALL_WO_SECTION"   => "Y",
//         "FILTER_NAME"           => 'smartFilter',
//         "PRICE_CODE"            => [],
//         "CURRENCY_ID"           => "",
//         "CONVERT_CURRENCY"      => "N",
//         "HIDE_NOT_AVAILABLE"    => "N",
//         "CACHE_TYPE"            => "N",
//         "CACHE_TIME"            => 3600,
//         "CACHE_GROUPS"          => "N",
//         "SAVE_IN_SESSION"       => "N",
//         "XML_EXPORT"            => "N",
//         "SEF_MODE"              => "N",
//         "SEF_RULE"              => null,
//     ], 
//     null, 
//     ['HIDE_ICONS' => 'Y'],
//     true // return result
// );
// ```