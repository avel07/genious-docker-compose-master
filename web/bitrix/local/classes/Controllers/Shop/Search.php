<?php

declare(strict_types=1);

namespace Cube\Controllers\Shop;

use Cube\Controllers\BaseController;
use Bitrix\Main\Loader;
use OpenApi\Attributes as OA;

#[OA\Tag('search')]
class Search extends BaseController
{
    /**
     * Поиск по сайту
     *
     * @param [type] $query
     * @return void
     */
    #[OA\Get(path: '/api/v1/search', tags: ['search'])]
    #[OA\QueryParameter(name: 'query')]
    #[OA\Response(response: 200, description: 'Результат поиска')]
    public function searchAction(string|int $iblockId, $query)
    {
        Loader::includeModule('search');

        $result = [
            'CATEGORIES' => [],
            'ITEMS'   => []
        ];

        $obSearch = new \CSearch();
        $obSearch->SetOptions([
            'ERROR_ON_EMPTY_STEM' => false,
        ]);

        $obSearch->Search(
            [
                'QUERY'     => $query,
                'SITE_ID'   => SITE_ID,
                'MODULE_ID' => 'iblock',
                'PARAM2'    => $iblockId
            ],
            [
                'TITLE_RANK' => 'DESC',
                'RANK'       => 'DESC'
            ]
        );
        if (!$obSearch->selectedRowsCount()) {
            $obSearch->Search(
                [
                    'QUERY'     => $query,
                    'SITE_ID'   => SITE_ID,
                    'MODULE_ID' => 'iblock',
                    'PARAM2'    => $iblockId
                ],
                [
                    'TITLE_RANK' => 'DESC',
                    'RANK'       => 'DESC'
                ],
                ['STEMMING' => false]
            );
        }

        $productIds = [];
        // Собираем ID товаров
        while ($aIndex = $obSearch->Fetch()) {
            // Если не является категорией
            if (strpos($aIndex['ITEM_ID'], 'S') === false) {
                $productIds[] = $aIndex['ITEM_ID'];
            }
        }
        $productIds = array_unique($productIds);

        // Передаем действие в контроллер каталога
        $result = $this->forward(\Cube\Controllers\Shop\Catalog::class, 'list', [
            'iblockId' => $iblockId,
            'filter' => [
                'ID' => $productIds
            ],
            'size' => 0
        ]);

        return $result;
    }
}
