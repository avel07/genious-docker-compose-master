<script setup lang="ts">
import type { PropType } from 'vue';
import SvgIconCounterPlus from '~/assets/img/counter-plus.svg?component';
import SvgIconCounterMinus from '~/assets/img/counter-minus.svg?component';

const props = defineProps({
    item: {
        type: Object as PropType<any>,
        default: {}
    }
});

const item = computed(() => {
    if (!props.item) {
        return null;
    }
    return {
        id: props.item?.PRODUCT_ID,
        code: props.item?.CODE,
        name: props.item?.NAME,
        title: props.item?.REAL_NAME,
        imageSrc: props.item?.PROPERTIES?.MORE_PHOTO[0],
        size: props.item?.PROPERTIES?.RAZMER?.VALUE,
        color: props.item?.PROPERTIES?.TSVET?.NAME,
        length: props.item?.PROPERTIES?.DLINA?.VALUE,
        price: props.item?.PRICES?.totalPriceFormat,
        quantity: props.item?.QUANTITY
    };
});

const basket = useBasket();
const deleteItem = (itemId: string | number) => {
    basket.delete(itemId);
};

const quantityChange = (itemId: string | number, quntity: number) => {
    basket.quantity(itemId, quntity);
};
</script>

<template>
    <div class="basket-card" v-if="item">
        <div class="basket-card__image">
            <img :src="item.imageSrc" :alt="item.title" />
        </div>
        <div class="basket-card__content">
            <div class="basket-card__top">
                <div class="g-row g-row_simple g-row_m-wrap">
                    <div class="basket-card__main g-col g-col_m-100">
                        <div class="basket-card__info">
                            <NuxtLink :to="`/product/${item.code}`" class="link uppercase fw-medium spacing">
                                {{ item.name }}
                            </NuxtLink>

                            <div class="mt-12 mt-m-4 fs-s-13 fw-medium">{{ item.title }}</div>
                            <div class="mt-12 mt-m-4 fs-s-13">
                                {{ item.color }} {{ item.size ? '/' + item.size : '' }}
                                {{ item.length ? '/' + item.length : '' }}
                            </div>
                        </div>
                    </div>

                    <div class="g-col g-col_same g-col_m-100 mt-m-24">
                        <div class="counter">
                            <div class="counter__data text-center fs-s-13">{{ item.quantity }}</div>

                            <button
                                type="button"
                                class="counter__button"
                                @click.prevent="quantityChange(item.id, item.quantity - 1)"
                            >
                                <SvgIconCounterMinus />
                            </button>

                            <button
                                type="button"
                                class="counter__button"
                                @click.prevent="quantityChange(item.id, item.quantity + 1)"
                            >
                                <SvgIconCounterPlus />
                            </button>
                        </div>
                    </div>

                    <div class="text-right g-col g-col_same g-col_m-100 mt-m-24 text-m-left">
                        <div class="nowrap fs-s-13 fw-medium spacing" v-html="item.price"></div>
                    </div>
                </div>
            </div>

            <div class="basket-card__bottom mt-m-16">
                <div class="g-row g-row_medium">
                    <div class="g-col">
                        <!-- TODO: вишлист -->
                        <button type="button" class="link link_underline text-light-grey fs-s-13">В вишлист</button>
                    </div>
                    <div class="g-col">
                        <button
                            type="button"
                            class="link link_underline text-light-grey fs-s-13"
                            @click.prevent="deleteItem(item.id)"
                        >
                            Удалить
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style lang="scss">
@import './basket-card.scss';
@import './counter.scss';
</style>
