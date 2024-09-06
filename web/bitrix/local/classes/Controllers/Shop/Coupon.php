<?php

declare(strict_types=1);

namespace Cube\Controllers\Shop;

use Cube\Controllers\BaseController;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Error;
use OpenApi\Attributes as OA;

#[OA\Tag('coupon')]
class Coupon extends BaseController
{
    /**
     * Добавить купон к корзине
     *
     * @param string $orderId
     * @return void
     */
    #[OA\Post(path: '/api/v1/coupon', tags: ['coupon'])]
    #[OA\QueryParameter(name: 'coupon', description: 'код купона', required: true, example: 'EXAMPLE_123')]
    #[OA\Response(response: 201, description: 'Купон успешно добавлен к корзине')]
    #[OA\Response(response: 400, description: 'Неверный запрос')]
    public function addAction(string $coupon): ?array
    {
        Loader::includeModule("sale");
        \Bitrix\Sale\DiscountCouponsManager::init();
        \Bitrix\Sale\DiscountCouponsManager::clear(true);
        $getCoupon = \Bitrix\Sale\DiscountCouponsManager::getData($coupon, true); // получаем информацио о купоне

        if ($getCoupon['ACTIVE'] == "Y") {
            $takeCoupon = \Bitrix\Sale\DiscountCouponsManager::add($coupon); // true - купон есть / false - его нет

            if ($takeCoupon) {
                Context::getCurrent()->getResponse()->setStatus(201);
                return [
                    'coupon' => $getCoupon['COUPON'],
                    'desc'   => $getCoupon['DISCOUNT_NAME']
                ];
            } else {
                $this->addError(new Error('Ошибка Активации купона', 'ERR_APPLY'));
                return null;
            }
        } else {
            $this->addError(new Error('Купон не найден', 'ERR_APPLY'));
            return null;
        }
    }

    /**
     * Удалить все купоны из корзины
     *
     * @return boolean
     */
    #[OA\Post(path: '/api/v1/coupon', tags: ['coupon'])]
    #[OA\Response(response: 200, description: 'Все купоны удалены из корзины')]
    #[OA\Response(response: 400, description: 'Неверный запрос')]
    public function clearAction(): bool
    {
        Loader::includeModule("sale");

        \Bitrix\Sale\DiscountCouponsManager::init();
        $result = \Bitrix\Sale\DiscountCouponsManager::clear(true);

        return $result;
    }
}
