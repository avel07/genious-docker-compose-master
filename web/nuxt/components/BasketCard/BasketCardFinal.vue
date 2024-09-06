<script setup lang="ts">
import type { PropType } from 'vue';

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
</script>

<template>
    <div class="basket-card basket-card_final" v-if="item">
        <div class="basket-card__image">
            <img :src="item.imageSrc" :alt="item.title" />
        </div>
        <div class="basket-card__content">
            <div class="basket-card__top">
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

            <div class="basket-card__bottom mt-m-16">
                <div class="nowrap fs-s-13 fw-medium spacing" v-html="item.price"></div>
            </div>
        </div>
    </div>
</template>

<style lang="scss">
@import './basket-card.scss';
</style>
