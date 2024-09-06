<?php

declare(strict_types=1);

namespace Cube\Controllers\Shop;

use Bitrix\Main\Loader;
use \Bitrix\Catalog\PriceTable;
use Cube\Controllers\BaseController;
use OpenApi\Attributes as OA;

#[OA\Tag('catalog')]
class Prices extends BaseController
{
    public const DEFAULT_USER_GROUP = [2];   // Все пользователи
    public const DEFAULT_PRICE_GROUP_ID = 1; // Тип цен BASE

    /**
     * Получение цен для товаров
     *
     * @return array|null
     */
    #[OA\Get(path: '/api/v1/prices', tags: ['catalog'])]
    #[OA\QueryParameter(name: 'ids[]', description: 'ID товаров', required: true, schema: new OA\Schema(type: 'array'))]
    #[OA\QueryParameter(name: 'groups[]', description: 'Группы пользователей (По умолчанию все)', schema: new OA\Schema(type: 'array'))]
    #[OA\QueryParameter(name: 'priceGroupId', description: 'ID получаемой цены (По умолчанию 1)')]
    #[OA\Response(response: 200, description: 'Цены со скидками на товары')]
    #[OA\Response(response: 400, description: 'Неверный запрос')]
    public function listAction(array $ids, array $groups = self::DEFAULT_USER_GROUP, $priceGroupId = self::DEFAULT_PRICE_GROUP_ID): ?array
    {
        Loader::includeModule('catalog');
        $result = [
            'prices' => [],
        ];

        $iteratorCollection = PriceTable::query()
            ->whereIn('PRODUCT_ID', $ids)
            ->where('CATALOG_GROUP_ID', $priceGroupId)
            ->addSelect('ID')
            ->addSelect('CATALOG_GROUP_ID')
            ->addSelect('PRICE')
            ->addSelect('CURRENCY')
            ->addSelect('PRODUCT_ID')
            ->addSelect('ELEMENT.IBLOCK_ID')
            ->fetchCollection();

        $isNeedDiscounts = \Bitrix\Catalog\Product\Price\Calculation::isAllowedUseDiscounts();
        foreach ($iteratorCollection as $price) {
            $productId = $price->get('PRODUCT_ID');
            if ($isNeedDiscounts == true && $price->get('PRICE') > 0) {
                // Получаем скидки на товар
                $discountList = \CCatalogDiscount::GetDiscount($productId, $price->get('ELEMENT')->get('IBLOCK_ID'), $price->get('CATALOG_GROUP_ID'), $groups);
                // Купоны не считаем
                foreach ($discountList as $key => $discount) {
                    if (!empty($discount['COUPON'])) {
                        unset($discountList[$key]);
                    }
                }

                // Применяем скидки и получаем итоговую цену
                $discountResult = \CCatalogDiscount::applyDiscountList($price->get('PRICE'), $price->get('CURRENCY'), $discountList);

                $discountPrice = $discountResult['PRICE'];
            } else {
                $discountPrice = $price->get('PRICE');
            }

            // Правила округления цен
            $basePrice = \Bitrix\Catalog\Product\Price::roundPrice($price->get('CATALOG_GROUP_ID'), $price->get('PRICE'), $price->get('CURRENCY'));
            $discountPrice = \Bitrix\Catalog\Product\Price::roundPrice($price->get('CATALOG_GROUP_ID'), $discountPrice, $price->get('CURRENCY'));

            $priceItem = [];
            $priceItem['productId']       = $productId;
            $priceItem['basePrice']       = $basePrice;
            $priceItem['basePriceFormat'] = \CCurrencyLang::CurrencyFormat($basePrice, $price->get('CURRENCY'));
            $priceItem['price']           = $discountPrice;
            $priceItem['priceFormat']     = \CCurrencyLang::CurrencyFormat($discountPrice, $price->get('CURRENCY'));

            $result['prices'][$productId] = $priceItem;
        }

        return $result;
    }
}
