<?php

declare(strict_types=1);

namespace Cube\Controllers\Shop;

use Bitrix\Main\Context;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Error;
use Cube\Controllers\BaseController;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Engine\UrlManager;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Page\AssetShowTargetType;
use Bitrix\Sale\Discount;
use OpenApi\Attributes as OA;

#[OA\Tag('order')]
class Order extends BaseController
{
    public const PARAMS = [
        "PAY_FROM_ACCOUNT"              => "N",                // Разрешить оплату с внутреннего счета
        "COUNT_DELIVERY_TAX"            => "N",
        "COUNT_DISCOUNT_4_ALL_QUANTITY" => "N",
        "ONLY_FULL_PAY_FROM_ACCOUNT"    => "N",                // Разрешить оплату с внутреннего счета только в полном объеме
        "ALLOW_AUTO_REGISTER"           => "Y",                // Оформлять заказ с автоматической регистрацией пользователя
        "SEND_NEW_USER_NOTIFY"          => "Y",                // Отправлять пользователю письмо, что он зарегистрирован на сайте
        "DELIVERY_NO_AJAX"              => "N",                // Когда рассчитывать доставки с внешними системами расчета
        "PATH_TO_BASKET"                => "/basket",          // Путь к странице корзины
        "PATH_TO_PERSONAL"              => "/order",           // Путь к странице персонального раздела
        "PATH_TO_PAYMENT"               => "/order/payment",   // Страница подключения платежной системы
        "PATH_TO_ORDER"                 => "/order",
        "SHOW_ACCOUNT_NUMBER"           => "Y",
        "DELIVERY_NO_SESSION"           => "N",                // Проверять сессию при оформлении заказа
    ];

    /**
     * Обработка заказа
     * аналог файла, для работы через контроллер /bitrix/components/bitrix/sale.order.ajax/ajax.php
     * @see https://gitlab.Cube.net/lighthouse/submodules/vue-sale-order-ajax
     *
     * @return void
     */
    #[OA\Post(path: '/api/v1/order', tags: ['order'])]
    #[OA\Response(response: 200, description: 'Оформление заказа')]
    #[OA\Response(response: 400, description: 'Неверный запрос')]
    public function ajaxAction()
    {
        if (!Loader::includeModule('sale')) {
            return;
        }

        Loc::loadMessages($_SERVER['DOCUMENT_ROOT'] . '/bitrix/components/bitrix/sale.order.ajax/ajax.php');

        /** @var \CMain $APPLICATION */
        global $APPLICATION;
        $APPLICATION->IncludeComponent(
            'bitrix:sale.order.ajax',
            '.default',
            self::PARAMS,
            null,
            [],
            true
        );

        $this->addError(new Error('Component error'));
        return null;
    }

    /**
     * Визуальный вывод оформленного заказа
     * Используем для iframe
     *
     * @param [type] $orderId
     * @return void
     */
    #[OA\Post(path: '/api/v1/order/{order}', tags: ['order'])]
    #[OA\Response(response: 200, description: 'Оформление заказа')]
    #[OA\Response(response: 400, description: 'Неверный запрос')]
    public function showAction($orderId)
    {
        if (!Loader::includeModule('sale')) {
            return;
        }

        Loc::loadMessages($_SERVER['DOCUMENT_ROOT'] . '/bitrix/components/bitrix/sale.order.ajax/ajax.php');

        $request = $this->getRequest();
        $request->set('ORDER_ID', $orderId);

        ob_start();
        /** @var \CMain $APPLICATION */
        global $APPLICATION;
        $APPLICATION->IncludeComponent(
            'bitrix:sale.order.ajax',
            '.default',
            self::PARAMS,
            null,
            [],
            true
        );
        $componentBuffer = ob_get_clean();

        ob_start();
        echo Asset::getInstance()->getJs(AssetShowTargetType::KERNEL);
        $jsBuffer = ob_get_clean();
        // Делаем ссылки абсолютными
        $basePath = UrlManager::getInstance()->getHostUrl();
        $buffer = str_replace(
            ['/bitrix/', '/upload/'],
            [$basePath . '/bitrix/', $basePath . '/upload/'],
            (string) $jsBuffer . (string) $componentBuffer
        );
        echo $buffer;

        return $buffer;
    }

    /**
     * Список заказов пользователя
     *
     * @return void
     */
    #[OA\Get(path: '/api/v1/orders/user', tags: ['order'])]
    #[OA\Response(response: 200, description: 'Список заказов')]
    #[OA\Response(response: 400, description: 'Неверный запрос')]
    public function listUserAction():? array
    {
        if (!Loader::includeModule('sale')) {
            Context::getCurrent()->getResponse()->setStatus(500);
            $this->addError(new Error('Module `sale` not found'));
            return null;
        }

        $userId = CurrentUser::get()?->getId();
        if (!$userId) {
            Context::getCurrent()->getResponse()->setStatus(401);
            $this->addError(new Error('Site login required'));
            return null;
        }

        // Коллекция заказов
        $ordersCollection = \Bitrix\Sale\Internals\OrderTable::query()
            ->where('USER_ID', $userId)
            ->addOrder('DATE_INSERT', 'DESC')
            ->addSelect('ID')
            ->addSelect('ACCOUNT_NUMBER')
            ->addSelect('DATE_INSERT')
            ->addSelect('PAYMENT.PAY_SYSTEM_NAME')
            ->addSelect('SHIPMENT.DELIVERY_NAME')
            ->addSelect('PRICE')
            ->addSelect('STATUS.NAME')
            ->addSelect('DATE_STATUS')
            ->fetchCollection();

        // Список ID заказов
        $orderIds = $ordersCollection->getIdList();
        $basketItems = \Bitrix\Sale\Internals\BasketTable::query()
            ->whereIn('ORDER_ID', $orderIds)
            ->addSelect('ORDER_ID')
            ->addSelect('PRODUCT_ID')
            ->addSelect('PRODUCT.IBLOCK.IBLOCK_ID', 'IBLOCK_ID')
            ->fetchAll();


        $iblockItems = [];
        foreach ($basketItems as $item) {
            $iblockId = $item['IBLOCK_ID'];
            $iblockItems[$iblockId][] = $item['PRODUCT_ID'];
        }

        $catalogItemsData = [];
        foreach ($iblockItems as $iblockId => $productIds) {
            $catalogItemsResult = $this->forward(\Cube\Controllers\Shop\Catalog::class, 'list', [
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
            foreach ($basketItems as &$item) {
                $productId = $item['PRODUCT_ID'];
                $itemData  = $catalogItemsData[$productId];
                $item['PROPERTIES'] = $itemData['PROPERTIES'] ?? null;

                if ($itemData && $parentId = $itemData['PROPERTIES']['CML2_LINK']) {
                    $item['NAME'] = $productData[$parentId]['NAME'];
                    $item['CODE'] = $productData[$parentId]['CODE'];
                    $item['PARENT_ID'] = $parentId;
                }
            }
            unset($item);
        }

        $result = [];
        foreach ($ordersCollection as $order) {
            $orderData = [
                'ID'             => $order->get('ID'),
                'ACCOUNT_NUMBER' => $order->get('ACCOUNT_NUMBER'),
                'DATE_INSERT'    => FormatDate("j F Y H:i", $order->get('DATE_INSERT')),
                'PAYMENT_NAME'   => $order->get('PAYMENT')?->get('PAY_SYSTEM_NAME'),
                'SHIPMENT_NAME'  => $order->get('SHIPMENT')?->get('DELIVERY_NAME'),
                'PRICE'          => $order->get('PRICE'), 
                'STATUS_NAME'    => $order->get('STATUS')?->get('NAME'),
                'DATE_STATUS'    => FormatDate("j F", $order->get('DATE_STATUS'))
            ];

            // Проставляем товары в заказ
            $orderData['PRODUCTS_DATA'] = array_filter($basketItems, fn ($item) => $item['ORDER_ID'] == $orderData['ID']);
            $result[] = $orderData;
        }

        return $result;
    }
}
