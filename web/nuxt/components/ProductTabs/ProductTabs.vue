<template>
    <div class="product-tabs">
        <div class="product-tabs__menu">
            <div class="g-row g-row_large g-row_s-small">
                <div class="g-col">
                    <div
                        class="product-tabs__link fs-s-12 fw-medium spacing uppercase nowrap"
                        :class="{ 'is-active': activeEl === 1 }"
                        @click="activeEl = 1"
                    >
                        Доставка и оплата
                    </div>
                </div>

                <div class="g-col">
                    <div
                        class="product-tabs__link fs-s-12 fw-medium spacing uppercase nowrap"
                        :class="{ 'is-active': activeEl === 2 }"
                        @click="activeEl = 2"
                    >
                        Детали
                    </div>
                </div>
            </div>
        </div>

        <div class="product-tabs__block pt-16" v-if="activeEl === 1">
            <div class="fs-16 fs-s-14">
                <p>
                    Beyond a season, a trend, a moment, to a more sustainable world. Discover a destination dedicated to
                    progress. Here, we want to share with you the whole story. The beginning, middle and beyond of
                    everything we do, everything we make. From each new innovative material to the suppliers we work
                    with. Beyond our collections, within our production chains and throughout our community.
                </p>
                <p>Вышеуказанные сроки доставки увеличиваются для отдаленных регионов.</p>
                <ul>
                    <li>
                        Cтандартная — 299 ₽ / Бесплатно для заказов от 3000 ₽ (распространяется только на товары без
                        скидки). Примерный срок доставки составит около 1-7 рабочих дней, в зависимости от адреса
                        доставки.
                    </li>
                    <li>
                        В день покупки — 590 руб. Доступна для жителей г. Москва в пределах МКАД. Заказы, оформленные с
                        понедельника по пятницу до 14:00 будут доставлены в тот же день с 19:00 до 23:00. Изменение
                        адреса доставки после оформления заказа невозможно.
                    </li>
                </ul>
            </div>
        </div>
        <div class="product-tabs__block pt-16" v-if="activeEl === 2">
            <div class="fs-16 fs-s-14">
                3.Beyond a season, a trend, a moment, to a more sustainable world. Discover a destination dedicated to
                progress. Here, we want to share with you the whole story. The beginning, middle and beyond of
                everything we do, everything we make. From each new innovative material to the suppliers we work with.
                Beyond our collections, within our production chains and throughout our community.
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            activeEl: 1
        };
    }
};
</script>

<style lang="scss">
@import './product-tabs.scss';
</style>
