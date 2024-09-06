<?php

declare(strict_types=1);

namespace Cube\Controllers\Shop;

use Cube\Controllers\BaseController;
use Bitrix\Main\Request;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Sale;
use OpenApi\Attributes as OA;

#[OA\Tag('basket')]
class Basket extends BaseController
{
    public $currentBasket;
    public $discounts;

    public function __construct(Request $request = null)
    {
        Loader::includeModule('sale');

        /** @var Sale\BasketBase */
        $this->currentBasket = Sale\Basket\Storage::getInstance(Sale\Fuser::getId(), Context::getCurrent()->getSite() ?? 's1')->getBasket();
        parent::__construct($request);
    }

    /**
     * Получить корзину
     *
     * @return array|null
     */
    #[OA\Get(path: '/api/v1/basket', tags: ['basket'])]
    #[OA\Response(response: 200, description: 'Корзина текущего пользователя')]
    #[OA\Response(response: 400, description: 'Неверный запрос')]
    public function listAction(): ?array
    {
        $result = [];
        $this->getDiscounts();

        if ($this->discounts) {
            $coupon = current($this->discounts['COUPON_LIST']);
            $result['coupon'] = $coupon['COUPON'];
        } else {
            $result['coupon'] = null;
        }

        $items = $this->getProductData();

        // Считаем итоговые цены
        foreach ($items as $item) {
            $basePriceItemFinal = $item['QUANTITY'] * $item['PRICES']['basePrice'];
            $priceItemFinal     = $item['QUANTITY'] * $item['PRICES']['price'];

            $result['priceItems'] += $basePriceItemFinal;
            $result['discount']   += $basePriceItemFinal - $priceItemFinal;
        }

        $result['total']            = $result['priceItems'] - $result['discount'];
        $result['priceItemsFormat'] = \CCurrencyLang::CurrencyFormat($result['priceItems'], 'RUB');
        $result['discountFormat']   = \CCurrencyLang::CurrencyFormat($result['discount'], 'RUB');
        $result['totalFormat']      = \CCurrencyLang::CurrencyFormat($result['total'], 'RUB');
        $result['items']            = $items;

        return $result;
    }

    /**
     * Получить количество позиций в корзне
     *
     * @return array
     */
    #[OA\Get(path: '/api/v1/basket/count', tags: ['basket'])]
    #[OA\Response(response: 200, description: 'количество позиций в корзине (намного быстрее)')]
    #[OA\Response(response: 400, description: 'Неверный запрос')]
    public function countAction(): array
    {
        $result = [
            'count' => $this->currentBasket->count()
        ];

        return $result;
    }

    /**
     * Добавить товар в корзину
     *
     * @param string $id
     * @param string $qty
     * @return array|null
     */
    #[OA\Post(path: '/api/v1/basket/add', tags: ['basket'])]
    #[OA\QueryParameter(name: 'id', description: 'id товара', required: true, example: 1)]
    #[OA\QueryParameter(name: 'qty', description: 'количество', example: 1)]
    #[OA\Response(response: 201, description: 'Товар добавлен в корзину')]
    #[OA\Response(response: 400, description: 'Неверный запрос')]
    public function addAction(string $id, string $qty = '1'): ?array
    {
        Loader::includeModule('sale');
        if (!$item = $this->getExists($id)) {
            $fields = [
                'PRODUCT_ID'             => $id,
                'QUANTITY'               => $qty,
                'MODULE'                 => 'catalog',
                'PRODUCT_PROVIDER_CLASS' => \Bitrix\Catalog\Product\Basket::getDefaultProviderName()
            ];

            $result = \Bitrix\Catalog\Product\Basket::addProductToBasketWithPermissions($this->currentBasket, $fields, ['SITE_ID' => Context::getCurrent()->getSite()], false);

            // Добавление объекта в корзину
            if (!$result->isSuccess()) {
                $this->addErrors($result->getErrors());
                return null;
            }

            $resultData = $result->getData();          // Данные при добавлении в коризну
            $item       = $resultData['BASKET_ITEM'];  // Получаем товар

            $result = $item->getBasket()->save();

            // Сохранение корзины
            if (!$result->isSuccess()) {
                $this->addError(new \Bitrix\Main\Error(current($result->getErrorMessages()), "ERR_RESULT"));
                return null;
            }
        }

        $result = [
            'action' => (int) $id,
            'basket' => $this->forward($this, 'list'),
        ];

        Context::getCurrent()->getResponse()->setStatus(201);
        return $result;
    }

    /**
     * Удалить товар из корзины
     *
     * @param string $id
     * @return array|null
     */
    #[OA\Post(path: '/api/v1/basket/delete', tags: ['basket'])]
    #[OA\QueryParameter(name: 'id', description: 'id товара', required: true, example: 1)]
    #[OA\Response(response: 200, description: 'Товар удален из корзины')]
    #[OA\Response(response: 400, description: 'Товара нет в корзине')]
    #[OA\Response(response: 400, description: 'Неверный запрос')]
    public function deleteAction(string $id): ?array
    {
        if ($item = $this->getExists($id)) {
            $item->delete();
        } else {
            Context::getCurrent()->getResponse()->setStatus(400);
            $this->addError(new \Bitrix\Main\Error('Товара нет в корзине', "NOT_FOUND"));
            return null;
        }

        $result = $item->getBasket()->save();

        if (!$result->isSuccess()) {
            $this->addError(new \Bitrix\Main\Error('Не удалось удалить товар из корзины', "ERR_RESULT"));
            return null;
        }

        $result = [
            'action' => (int) $id,
            'basket' => $this->forward($this, 'list'),
        ];

        return $result;
    }

    /**
     * Изменить кол-во товара в корзине
     *
     * @param string $id
     * @param string $qty
     * @return array|null
     */
    #[OA\Post(path: '/api/v1/basket/quantity', tags: ['basket'])]
    #[OA\QueryParameter(name: 'id', description: 'id товара', required: true, example: 1)]
    #[OA\QueryParameter(name: 'qty', description: 'количество товара', required: true, example: 1)]
    #[OA\Response(response: 200, description: 'количество товара изменено')]
    #[OA\Response(response: 400, description: 'Товара нет в корзине')]
    #[OA\Response(response: 400, description: 'Неверный запрос')]
    public function quantityAction(string $id, string $qty): ?array
    {
        if ($item = $this->getExists($id)) {
            $iterator = \Bitrix\Catalog\ProductTable::query()
                ->where('ID', $item->getField('PRODUCT_ID'))
                ->addSelect('QUANTITY')
                ->addSelect('CAN_BUY_ZERO')
                ->fetchObject();
            if ($iterator && $qty > 0) {
                // Разрешена ли покупка при отсутствии товара и кол-во больше чем имеется
                if ($iterator->get('CAN_BUY_ZERO') === 'N' && $iterator->get('QUANTITY') < $qty) {
                    $item->setField('QUANTITY', $iterator->get('QUANTITY'));
                } else {
                    $item->setField('QUANTITY', $qty);
                }
            } else {
                return $this->deleteAction($id);
            }
        } else {
            Context::getCurrent()->getResponse()->setStatus(400);
            $this->addError(new \Bitrix\Main\Error('Товара нет в корзине', "NOT_FOUND"));
            return null;
        }

        $result = $item->getBasket()->save();

        if (!$result->isSuccess()) {
            $this->addError(new \Bitrix\Main\Error('Не удалось удалить товар из корзины', "ERR_RESULT"));
            return null;
        }

        $result = [
            'action' => (int) $id,
            'basket' => $this->forward($this, 'list'),
        ];

        return $result;
    }

    /**
     * Ищем товар в корзине
     *
     * @param [type] $productId
     * @return object|null
     */
    protected function getExists($productId): ?object
    {
        $basketItems = $this->currentBasket->getBasketItems();

        foreach ($basketItems as $item) {
            if ($item->getField('PRODUCT_ID') == $productId) {
                return $item;
            }
        }

        return null;
    }

    protected function getDiscounts()
    {
        // Получаем скидки корзины для текущего пользователя\
        $discounts = \Bitrix\Sale\Discount::buildFromBasket($this->currentBasket, new \Bitrix\Sale\Discount\Context\Fuser($this->currentBasket->getFUserId(true)));
        if ($discounts) {
            $discounts->calculate();
            $this->discounts = $discounts->getApplyResult(true);
        }
    }

    /**
     * Вывод товаров в одинаковом формате товара и получение цен со скидками
     *
     * @return array|null
     */
    protected function getProductData(): ?array
    {
        $basketItems = $this->currentBasket->getBasketItems();
        if (empty($basketItems)) {
            return [];
        }

        $productIds = [];
        foreach ($basketItems as $item) {
            $productIds[] = $item->getField('PRODUCT_ID');
        }

        $productData = \Bitrix\Catalog\ProductTable::query()
            ->whereIn('ID', $productIds)
            ->addSelect('AVAILABLE')
            ->addSelect('IBLOCK_ELEMENT.IBLOCK_ID')
            ->fetchCollection();

        // Удаление недоступных товаров
        $isDeleteNotAvailable = false;
        foreach ($basketItems as $item) {
            $canBuyObject = $productData->getByPrimary($item->getField('PRODUCT_ID'));
            if ($canBuyObject && !$canBuyObject->getAvailable()) {
                $item->delete();
                $isDeleteNotAvailable = true;
            }
        }

        // Если есть удаленные, запускаем заново метод
        if ($isDeleteNotAvailable === true) {
            $item->getBasket()->save();
            return $this->getProductData();
        }

        $result = [];
        $basket = $this->currentBasket;

        // Получаем скидки корзины для текущего пользователя или от заказа
        if ($basket->getOrder()) {
            $discounts = \Bitrix\Sale\Discount::buildFromOrder($basket->getOrder());
        } else {
            $discounts = \Bitrix\Sale\Discount::buildFromBasket($basket, new \Bitrix\Sale\Discount\Context\Fuser($basket->getFUserId(true)));
        }
        $discounts->calculate();

        // Получаем скидки и цены со скидками
        $arBasketDiscounts = $discounts->getApplyResult(true);
        $basketPrices = $arBasketDiscounts['PRICES']['BASKET'];

        // Разбираем обьект полей товара в корзине
        foreach ($basketItems as $basketItem) {
            // Поля товара корзины
            $basketItemFeilds = $basketItem->getFields()->getValues();

            // Добавляем поля корзины и скидок для товара
            $itemBasket = [
                'NAME'             => $basketItemFeilds['NAME'],
                'PRODUCT_ID'       => (int) $basketItemFeilds['PRODUCT_ID'],
                'IBLOCK_ID'        => (int) $productData?->getByPrimary($item->getField('PRODUCT_ID'))?->get('IBLOCK_ELEMENT')?->get('IBLOCK_ID'),
                'QUANTITY'         => (int) $basketItemFeilds['QUANTITY'],
            ];

            $internalId = $basketItem->getBasketCode();
            $itemBasket['PRICES'] = [
                'basePrice'        => $basketPrices[$internalId]['BASE_PRICE'],
                'basePriceFormat'  => \CCurrencyLang::CurrencyFormat($basketPrices[$internalId]['BASE_PRICE'], 'RUB'),
                'price'            => $basketPrices[$internalId]['PRICE'],
                'priceFormat'      => \CCurrencyLang::CurrencyFormat($basketPrices[$internalId]['PRICE'], 'RUB'),
                'totalPrice'       => (int) $basketItemFeilds['QUANTITY'] * $basketPrices[$internalId]['PRICE'],
                'totalPriceFormat' => \CCurrencyLang::CurrencyFormat((int) $basketItemFeilds['QUANTITY'] * $basketPrices[$internalId]['PRICE'], 'RUB')
            ];
        
            $result[] = $itemBasket;
        }

        return $result;
    }
}
