<?php

declare(strict_types=1);

namespace Cube\Controllers\Shop;

use Cube\Controllers\BaseController;
use Bitrix\Main\Request;
use Bitrix\Main\Loader;
use OpenApi\Attributes as OA;

/**
 * Кастомное меню для логикик genious
 */
#[OA\Tag('filter')]
class Filter extends BaseController
{
    // В arResult нужно отдать результат в переменную
    // Cube\Controllers\Shop\Filter::$resultFilter = $arResult
    public static $resultFilter = [];


    /**
     * Constructor Controller.
     * @param Request|null $request
     */
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
    }

    /**
     * Умный фильтр (Для фильтрации по свойству передаем любой `CONTROL_ID` в request с значением `Y`)
     *
     * @return array|null
     */
    #[OA\Get(path: '/api/v1/filter', tags: ['filter'])]
    #[OA\QueryParameter(name: 'filter_29_128296860', example: 'Y')]
    #[OA\QueryParameter(name: 'sectionCode', example: 'hailes', description: 'Код раздела в котором отрабатывает фильтр')]
    #[OA\Response(response: 200, description: 'Структура меню')]
    #[OA\Response(response: 404, description: 'Не найдено')]
    #[OA\Response(response: 400, description: 'Неверный запрос')]
    public function listAction(string|int $iblockId, string|null $sectionCode = null): ?array
    {
        Loader::includeModule("iblock");

        // Всегда режим фильтра
        $_GET['set_filter'] = 'Y';

        /** @var \CMain $APPLICATION */
        global $APPLICATION;

        // Результат меняем через result_modifier.php
        $filterResult = (array) $APPLICATION->IncludeComponent(
            "bitrix:catalog.smart.filter",
            "json",
            [
                "IBLOCK_ID"             => $iblockId,
                "SECTION_ID"            => null,
                "SECTION_CODE"          => $sectionCode,
                "SHOW_ALL_WO_SECTION"   => "Y",
                "FILTER_NAME"           => 'filter',
                "PRICE_CODE"            => [],
                "CURRENCY_ID"           => "",
                "CONVERT_CURRENCY"      => "N",
                "HIDE_NOT_AVAILABLE"    => "N",
                "CACHE_TYPE"            => "A",
                "CACHE_TIME"            => 3600,
                "CACHE_GROUPS"          => "Y",
                "SAVE_IN_SESSION"       => "N",
                "XML_EXPORT"            => "N",
                "SEF_MODE"              => "N",
                "SEF_RULE"              => null,
            ],
            null,
            [],
            true // return result
        );

        return $filterResult;
    }
}
