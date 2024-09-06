<?php

require_once __DIR__ . "/../../bitrix/vendor/autoload.php";

use Bitrix\Main\Routing\RoutingConfigurator;

return function (RoutingConfigurator $routes) {
    /** CATALOG */
    // Получение каталога
    $routes->get('/api/v1/catalog', fn () => (new \Cube\Controllers\Shop\Catalog())->runAction('list'));
    // Получение разделов
    $routes->get('/api/v1/catalog/sections', fn () => (new \Cube\Controllers\Shop\Catalog())->runAction('sectionList'));
    // Получение товаров раздела
    $routes->get('/api/v1/catalog/section/{code}', fn () => (new \Cube\Controllers\Shop\Catalog())->runAction('sectionItems'))->default('iblockId', \Cube\Controllers\Shop\Catalog::CATLOG_IBLOCK_ID);
    // Получение элемента каталога
    $routes->get('/api/v1/catalog/product/{code}', fn () => (new \Cube\Controllers\Shop\Catalog())->runAction('get'))->default('iblockId', \Cube\Controllers\Shop\Catalog::CATLOG_IBLOCK_ID);
    // Получение торговых предложений
    $routes->get('/api/v1/offers', fn () => (new \Cube\Controllers\Shop\Catalog())->runAction('list'))->default('iblockId', \Cube\Controllers\Shop\Catalog::OFFERS_IBLOCK_ID);
    // Получение цен со скидками для элементов
    $routes->get('/api/v1/prices', fn () => (new \Cube\Controllers\Shop\Prices())->runAction('list'));

    /** FILTER */
    // Получить меню по символьному коду
    $routes->get('/api/v1/filter', fn () => (new \Cube\Controllers\Shop\Filter())->runAction('list'))->default('iblockId', \Cube\Controllers\Shop\Catalog::CATLOG_IBLOCK_ID);

    /** MENU */
    // Получить все меню
    $routes->get('/api/v1/menu', fn () => (new \Cube\Controllers\Content\Menu())->runAction('all'));
    // Получить меню разделов по символьному коду
    $routes->get('/api/v1/menu/sections', fn () => (new \Cube\Controllers\Content\Menu())->runAction('sectionsList'));
    // Получить меню по символьному коду
    $routes->get('/api/v1/menu/{code}', fn () => (new \Cube\Controllers\Content\Menu())->runAction('list'));

    /** USER */
    // Регистрация пользователя
    $routes->post('/api/v1/user/register', fn () => (new \Cube\Controllers\User())->runAction('register'));
    // Авторизация пользователя
    $routes->post('/api/v1/user/login', fn () => (new \Cube\Controllers\User())->runAction('login'));
    // Выход из системы
    $routes->post('/api/v1/user/logout', fn () => (new \Cube\Controllers\User())->runAction('logout'));
    // Получить пользователя
    $routes->get('/api/v1/user', fn () => (new \Cube\Controllers\User())->runAction('get'));
    // Обновить пользователя
    $routes->post('/api/v1/user', fn () => (new \Cube\Controllers\User())->runAction('update'));
    // Запросить смену пароля
    $routes->post('/api/v1/user/passwordSend', fn () => (new \Cube\Controllers\User())->runAction('passwordSend'));
    // Смена пароля с контрольной строкой
    $routes->post('/api/v1/user/passwordChange', fn () => (new \Cube\Controllers\User())->runAction('passwordChange'));

    /** CONTENT */
    // Получение контент-страницы
    $routes->get('/api/v1/content/{pageCode}', fn () => (new \Cube\Controllers\Content\Content())->runAction('get'));
    // Получение всех статей
    $routes->get('/api/v1/blog', fn () => (new \Cube\Controllers\Content\Blog())->runAction('list'));
    // Получение статьи
    $routes->get('/api/v1/blog/{code}', fn () => (new \Cube\Controllers\Content\Blog())->runAction('get'));
    // Получение страницы вопросов и ответов
    $routes->get('/api/v1/faq', fn () => (new \Cube\Controllers\Content\Faq())->runAction('get'));
    // Получение главной страницы
    $routes->get('/api/v1/main', fn () => (new \Cube\Controllers\Content\Main())->runAction('get'));
    // Получение главной страницы
    $routes->get('/api/v1/collection/{pageCode}', fn () => (new \Cube\Controllers\Content\Collection())->runAction('get'));

    /** BASKET */
    // Получить корзину текущего пользователя
    $routes->get('/api/v1/basket', fn () => (new \Cube\Controllers\Shop\Basket())->runAction('list'));
    // Добавить товар в корзину
    $routes->post('/api/v1/basket/add', fn () => (new \Cube\Controllers\Shop\Basket())->runAction('add'));
    // Удалить товар из корзины
    $routes->post('/api/v1/basket/delete', fn () => (new \Cube\Controllers\Shop\Basket())->runAction('delete'));
    // Изменить кол-во товара в коризне
    $routes->post('/api/v1/basket/quantity', fn () => (new \Cube\Controllers\Shop\Basket())->runAction('quantity'));
    // Получить колличество позиций в корзне
    $routes->get('/api/v1/basket/count', fn () => (new \Cube\Controllers\Shop\Basket())->runAction('count'));

    /** COUPONS */
    // Применение купона
    $routes->post('/api/v1/coupon', fn () => (new \Cube\Controllers\Shop\Coupon())->runAction('add'));
    // Очистка купонов
    $routes->post('/api/v1/coupon', fn () => (new \Cube\Controllers\Shop\Coupon())->runAction('clear'));

    /** ORDER */
    // Калькуляция, оформлени
    $routes->post('/api/v1/order', fn () => (new \Cube\Controllers\Shop\Order())->runAction('ajax'));
    // Показать оформленный заказ
    $routes->get('/api/v1/order/{orderId}', fn () => (new \Cube\Controllers\Shop\Order())->runAction('show'));
    // Показать список заказов пользователя
    $routes->get('/api/v1/orders/user', fn () => (new \Cube\Controllers\Shop\Order())->runAction('listUser'));

    /** LOCATIONS */
    // Получить города
    $routes->get('/api/v1/location/cities', fn () => (new \Cube\Controllers\Shop\Location())->runAction('getCities'));
    // Получить страны
    $routes->get('/api/v1/location/counties', fn () => (new \Cube\Controllers\Shop\Location())->runAction('getCounties'));

    /** OTHER */
    // Поиск
    $routes->get('/api/v1/search', fn () => (new \Cube\Controllers\Shop\Search())->runAction('search'))->default('iblockId', \Cube\Controllers\Shop\Catalog::CATLOG_IBLOCK_ID);
    // Генерация openapi
    $routes->get('/api/v1/openapi', fn () => (new \Cube\Controllers\OpenApi())->runAction('generate'));
    // Подписка на рассылку
    $routes->post('/api/v1/subscribe', fn () => (new \Cube\Controllers\Subscribe())->runAction('subscribe'));
    // Получить данные формы
    $routes->get('/api/v1/webform/{formId}', fn () => (new \Cube\Controllers\WebForm())->runAction('getForm'));
    // Отправить форму
    $routes->post('/api/v1/webform/{formId}', fn () => (new \Cube\Controllers\WebForm())->runAction('setResult'));
};
