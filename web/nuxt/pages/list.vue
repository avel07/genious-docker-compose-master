<template>
    <main class="page">
        <div class="content">
            <div class="fs-32 fw-medium fs-s-21 uppercase">Pages</div>
            <div class="mt-4">
                <NuxtLink to="/main" class="link uppercase spacing">Главная</NuxtLink>
            </div>
            <div class="mt-4">
                <NuxtLink to="/catalog" class="link uppercase spacing">Каталог</NuxtLink>
            </div>
            <div class="mt-4">
                <NuxtLink to="/product" class="link uppercase spacing">Карточка товара</NuxtLink>
            </div>
            <div class="mt-4">
                <NuxtLink to="/search" class="link uppercase spacing">Результат поиска</NuxtLink>
            </div>
            <div class="mt-4">
                <NuxtLink to="/search-empty" class="link uppercase spacing">Результат поиска / пустой</NuxtLink>
            </div>
            <div class="mt-4">
                <NuxtLink to="/basket" class="link uppercase spacing">Корзина</NuxtLink>
            </div>
            <div class="mt-4">
                <NuxtLink to="/registration" class="link uppercase spacing">Регистрация</NuxtLink>
            </div>
            <div class="mt-4">
                <NuxtLink to="/basket-details" class="link uppercase spacing">Корзина / Детали заказа</NuxtLink>
            </div>
            <div class="mt-4">
                <NuxtLink to="/basket-final" class="link uppercase spacing">Успешная оплата</NuxtLink>
            </div>
            <div class="mt-4">
                <NuxtLink to="/return" class="link uppercase spacing">Возврат</NuxtLink>
            </div>
            <div class="mt-4">
                <NuxtLink to="/delivery" class="link uppercase spacing">Оплата и Доставка</NuxtLink>
            </div>
            <div class="mt-4">
                <NuxtLink to="/contacts" class="link uppercase spacing">Контакты</NuxtLink>
            </div>
            <div class="mt-4">
                <NuxtLink to="/company" class="link uppercase spacing">О нас</NuxtLink>
            </div>
            <div class="mt-4">
                <NuxtLink to="/offer" class="link uppercase spacing">Оферта</NuxtLink>
            </div>
            <div class="mt-4">
                <NuxtLink to="/recommendations" class="link uppercase spacing">Уход за материалами</NuxtLink>
            </div>
            <div class="mt-4">
                <NuxtLink to="/faq" class="link uppercase spacing">Вопросы и ответы</NuxtLink>
            </div>
            <div class="mt-4">
                <NuxtLink to="/404" class="link uppercase spacing">404</NuxtLink>
            </div>
            <div class="mt-4">
                <NuxtLink to="/500" class="link uppercase spacing">500</NuxtLink>
            </div>
            <div class="mt-4">
                <NuxtLink to="/articles" class="link uppercase spacing">Проекты</NuxtLink>
            </div>
            <div class="mt-4">
                <NuxtLink to="/article" class="link uppercase spacing">Проект</NuxtLink>
            </div>
            <div class="mt-4">
                <NuxtLink to="/wishlist" class="link uppercase spacing">Вишлист</NuxtLink>
            </div>
            <div class="mt-4">
                <NuxtLink to="/account" class="link uppercase spacing">настройки профиля</NuxtLink>
            </div>
            <div class="mt-4">
                <NuxtLink to="/orders" class="link uppercase spacing">заказы</NuxtLink>
            </div>
            <div class="mt-4">
                <NuxtLink to="/adresses" class="link uppercase spacing">адреса</NuxtLink>
            </div>
            <div class="mt-4">
                <NuxtLink to="/collection" class="link uppercase spacing">Коллекция</NuxtLink>
            </div>

            <div class="fs-32 fw-medium fs-s-21 uppercase mt-56">buttons</div>
            <div class="mt-8">
                <button class="button button_primary uppercase button_lg button_s-lg">
                    button button_primary uppercase button_lg button_s-lg
                </button>
            </div>

            <div class="mt-8">
                <button class="button button_secondary uppercase">button button_secondary uppercase</button>
            </div>

            <div class="mt-8">
                <button class="button button_dark-border uppercase button_sm button_s-sm">
                    button button_dark-border uppercase button_sm button_s-sm
                </button>
            </div>

            <div class="mt-8 pl-16 pr-16 pt-12 pb-12 bg-black">
                <button class="button button_white-border uppercase button_sm button_s-sm">
                    button button_white-border uppercase button_sm button_s-sm
                </button>
            </div>
        </div>
    </main>
</template>

<script>
export default {
    name: 'BasketFinalPage'
};
</script>
