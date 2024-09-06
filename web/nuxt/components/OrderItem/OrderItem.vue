<script setup lang="ts">
import type { PropType } from 'vue';

const props = defineProps({
    number: {
        type: String,
        default: ''
    },
    status: {
        type: String,
        default: ''
    },
    dataStatus: {
        type: String,
        default: ''
    },
    dataStart: {
        type: String,
        default: ''
    },
    payment: {
        type: String,
        default: ''
    },
    delivery: {
        type: String,
        default: ''
    },
    adress: {
        type: String,
        default: ''
    },
    imageName: {
        type: String,
        default: ''
    },
    title: {
        type: String,
        default: ''
    },
    price: {
        type: String,
        default: ''
    },
    discount: {
        type: String,
        default: ''
    },
    products: {
        type: Object as PropType<any>,
        default: []
    }
});

const productsData = computed(() => {
    let productsList = [];
    if (props.products) {
        for (const key in props.products) {
            if (props.products?.[key]) {
                const element = props.products[key];
                productsList.push({
                    CODE: element?.CODE,
                    PHOTO: element?.PROPERTIES?.MORE_PHOTO[0]
                });
            }
        }
    }
    return productsList;
});
</script>

<template>
    <div class="order">
        <div class="g-row g-row_t-wrap">
            <div class="g-col g-col_37 g-col_t-50 g-col_s-100 pt-24 pt-t-4">
                <div class="order__info">
                    <div class="fs-16 fw-medium uppercase t-hidden spacing">Заказ №{{ number }}</div>
                    <div class="mt-4 fw-medium t-hidden">{{ status }}: {{ dataStatus }}</div>

                    <div class="pt-12 pt-t-0">
                        <div class="mt-20 text-light-grey">Дата оформления</div>
                        <div class="mt-4">
                            {{ dataStart }}
                        </div>

                        <div class="mt-20 mt-m-12 text-light-grey">Cпособ оплаты</div>
                        <div class="mt-4">
                            {{ payment }}
                        </div>

                        <div class="mt-20 mt-m-12 text-light-grey">Cпособ получения</div>
                        <div class="mt-4">
                            {{ delivery }}
                        </div>

                        <!-- <div class="mt-20 mt-m-12 text-light-grey">Адрес получения</div>
                        <div class="mt-4">
                            {{ adress }}
                        </div> -->
                    </div>
                </div>
            </div>

            <div class="order__products g-col g-col_full g-col_t-100 pt-4">
                <div class="fs-16 fw-medium uppercase t-show spacing">Заказ №{{ number }}</div>
                <div class="mt-4 fw-medium t-show">{{ status }}: {{ dataStatus }}</div>

                <div class="g-row g-row_wrap g-row_medium g-row_s-small pt-t-4">
                    <div class="g-col pt-20" v-for="product in productsData" :key="product.CODE">
                        <NuxtLink :to="`/product/${product.CODE}`" class="order__image">
                            <img :src="`${product.PHOTO}`" :alt="product.CODE" />
                        </NuxtLink>
                    </div>
                </div>
            </div>

            <div class="g-col g-col_33 g-col_t-50 g-col_s-100 pt-24">
                <div class="order__content">
                    <div class="order__top text-right text-s-left">
                        <div class="nowrap fs-16 fs-s-13 fw-medium spacing">{{ price }} ₽</div>
                        <div class="fs-s-13 fw-medium mt-4">{{ discount }}</div>
                    </div>

                    <div class="order__bottom pt-40">
                        <div class="g-row g-row_medium g-row_end g-row_s-start">
                            <!-- <div class="g-col">
                                <button type="button" class="link link_underline text-light-grey">
                                    Повторить заказ
                                </button>
                            </div> -->
                            <div class="g-col">
                                <NuxtLink to="/return" type="button" class="link link_underline text-light-grey"
                                    >Вернуть заказ</NuxtLink
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style lang="scss">
@import './order-item.scss';
</style>
