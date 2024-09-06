<script setup lang="ts">
import { Swiper, SwiperSlide } from 'swiper/vue';
import type { PropType } from 'vue';

import 'swiper/css';
import 'swiper/css/pagination';
import 'swiper/swiper.min.css';

import { Pagination, Navigation } from 'swiper';

const modules = [Pagination, Navigation];

const props = defineProps({
    ids: {
        type: Array<number | string>,
        required: true
    }
});

// Получение каталога
const { data: catalog, error } = await useFetchApi(`/api/v1/catalog`, {
    method: 'GET',
    params: {
        filter: {
            ID: props.ids
        },
        size: 0 // all
    },
    key: 'api-catalog-recommends', // Кеш для гидрации
    transform: (response: any) => ({
        items: response?.data?.items,
        pagination: {
            currentPage: response?.data?.page,
            totalPages: Math.ceil(response?.data?.total / response?.data?.size),
            sizePage: response?.data?.size,
            totalItems: response?.data?.total
        }
    }),
    lazy: true
});

// Если ошибка от API товаров
if (isRef(error) && error.value) {
    let fetchError = error.value;
    throw createError({
        statusCode: fetchError.statusCode,
        statusMessage: fetchError.statusText,
        data: fetchError,
        fatal: true
    });
}

// Получаем цены
const itemsIds = computed(() => catalog.value?.items.map((item: any) => item.ID));
const { data: prices } = await useFetchApi(`/api/v1/prices`, {
    method: 'GET',
    params: {
        ids: itemsIds
    },
    key: 'api-prices-recommends', // Кеш для гидрации
    transform: (response: any) => response?.data?.prices,
    lazy: true
});

// Тороговые предложения
const { data: offersColors } = await useFetchApi(`/api/v1/offers`, {
    method: 'GET',
    params: {
        filter: {
            'CML2_LINK.VALUE': itemsIds
        },
        size: 0 // all
    },
    key: 'api-offers-colors-recommends', // Кеш для гидрации
    transform: (response: any) => {
        let offers = response?.data?.items?.map((offer: any) => ({
            PARENT_ID: offer.PROPERTIES?.CML2_LINK,
            COLOR: offer.PROPERTIES?.TSVET?.HEX
        }));

        // Оставляем только уникальные цвета для каждого товара и те, которые имеют HEX
        return offers?.filter((obj: any, index: number, self: any) => {
            return (
                obj?.COLOR &&
                index ===
                    self.findIndex((objDeep: any) => objDeep.COLOR === obj.COLOR && objDeep.PARENT_ID === obj.PARENT_ID)
            );
        });
    },
    server: false, // Выполняем только на клиенте
    lazy: true
});

// Объединяем товары с ценами и тп в единый объект
const items = computed(() => {
    let result = catalog.value?.items;
    for (const key in result) {
        if (Object.prototype.hasOwnProperty.call(result, key)) {
            const item = result[key];
            let itemId = item.ID;
            let photos = item?.PROPERTIES?.MORE_PHOTO;
            item['PHOTOS'] = null;
            if (photos) {
                item['PHOTOS'] =
                    typeof photos === 'object' ? item?.PROPERTIES?.MORE_PHOTO : [item?.PROPERTIES?.MORE_PHOTO];
            }
            item['PRICES'] =
                prices.value && Object.prototype.hasOwnProperty.call(prices.value, itemId)
                    ? prices.value[itemId]
                    : null;
            item['COLORS'] = offersColors.value
                ? offersColors.value.filter((obj: any) => obj?.PARENT_ID == itemId)
                : null;
        }
    }
    return result;
});
</script>

<template>
    <swiper
        :pagination="{ type: 'progressbar' }"
        :slidesPerView="'auto'"
        :navigation="true"
        :modules="modules"
        :breakpoints="{
            '577': {
                spaceBetween: 20
            }
        }"
        class="slider slider_products slider_products-add"
    >
        <swiper-slide class="slider__item" v-for="item in items" :key="item.ID">
            <ProductCardSlider
                :productId="item.ID"
                :productName="item.NAME"
                :productImage="item.PHOTOS"
                :productPrice="item.PRICES?.priceFormat"
                :productPriceOld="item.PRICES?.basePrice > item.PRICES?.price ? item.PRICES?.basePriceFormat : null"
                :link="'/product/' + item.CODE"
                :colors="item.COLORS"
            />
        </swiper-slide>
    </swiper>
</template>

<style lang="scss">
@import './slider.scss';
</style>
