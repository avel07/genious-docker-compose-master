<script setup lang="ts">
const basket = useBasket();
const data = await basket.load();

if (data.value?.items?.length <= 0) {
    navigateTo('/catalog');
}

const items = computed(() => {
    return data.value?.items;
});

// Отслеживаем сколько сейчас товаров в корзине
watch(items, (items) => {
    if (items?.length <= 0) {
        navigateTo('/catalog');
    }
});

// Meta head
useHead({
    title: 'Корзина'
});
</script>

<template>
    <main class="page">
        <div class="content content_small" v-if="data && data.items.length">
            <BasketMenu classItem="is-step-first" />

            <div class="mt-56 mt-m-30">
                <div class="basket-table">
                    <div class="basket-table__header m-hidden">
                        <div class="g-row g-row_simple fw-medium spacing">
                            <div class="g-col g-col_50 g-col_d-66">
                                <div class="uppercase">Продукт</div>
                            </div>
                            <div class="g-col g-col_same">
                                <div class="uppercase">Количество</div>
                            </div>
                            <div class="g-col g-col_same">
                                <div class="uppercase text-right">цена</div>
                            </div>
                        </div>
                    </div>

                    <div class="basket-table__body">
                        <BasketCard v-for="item in items" :key="item.PRODUCT_ID" :item="item" />
                    </div>
                </div>
            </div>

            <div class="g-row g-row_end g-row_simple">
                <div class="g-col g-col_50 g-col_t-100">
                    <div class="mt-40">
                        <PromoForm />
                    </div>

                    <div class="mt-40 mt-m-24 g-row g-row_baseline g-row_between">
                        <div class="g-col">
                            <div class="fs-16 fs-m-14">Конечная стоимость</div>
                        </div>
                        <div class="g-col">
                            <span
                                class="nowrap fs-16 fs-m-14 line-through"
                                v-if="data.priceItems > data.total"
                                v-html="data.priceItemsFormat"
                            ></span>
                            <span
                                class="nowrap fs-16 fs-m-14 ml-16"
                                :class="{ 'text-red': data.priceItems > data.total }"
                                v-html="data.totalFormat"
                            ></span>
                        </div>
                    </div>

                    <div class="mt-40">
                        <NuxtLink
                            to="/order"
                            class="button button_primary button_lg button_full uppercase fs-s-10 button_s-lg"
                        >
                            Перейти к оформлению
                        </NuxtLink>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<style lang="scss">
.basket-table {
    &__header {
        border-bottom: 1px solid #a8a8a8;
        padding-bottom: 15px;
    }
}
</style>
