<script setup lang="ts">
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Navigation, Pagination } from 'swiper';

import 'swiper/css';
import 'swiper/css/pagination';
import 'swiper/swiper.min.css';

const props = defineProps({
    products: {
        type: Array<any>,
        default: [],
        required: true
    },
    text: {
        default: ''
    }
});

// Получаем цены
const itemsIds = computed(() => props.products.map((item: any) => item.ID));
const { data: prices } = await useFetchApi(`/api/v1/prices`, {
    method: 'GET',
    params: {
        ids: itemsIds
    },
    key: 'api-prices', // Кеш для гидрации
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
    key: 'api-offers-colors', // Кеш для гидрации
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
    let result = props.products;
    for (const key in result) {
        if (Object.prototype.hasOwnProperty.call(result, key)) {
            const item = result[key];
            let itemId = item.ID;
            let photos = item?.PROPERTIES?.MORE_PHOTO;
            item['PHOTO'] = null;
            if (photos) {
                item['PHOTO'] = typeof photos === 'object' ? photos[0] : photos;
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

const components = [Swiper, SwiperSlide];
const modules = [Pagination, Navigation];
</script>

<template>
    <div class="content">
        <div class="uppercase fs-s-13 fw-medium spacing">{{ text }}</div>

        <div class="mt-32 mt-m-16">
            <swiper
                :pagination="{ type: 'progressbar' }"
                :slidesPerView="1"
                :navigation="true"
                :modules="modules"
                :breakpoints="{
                    '577': {
                        spaceBetween: 20,
                        slidesPerView: 2
                    },
                    '993': {
                        spaceBetween: 20,
                        slidesPerView: 4
                    }
                }"
                class="slider slider_products slider_products-main"
            >
                <swiper-slide v-for="item in items" :key="item.ID">
                    <ProductCard
                        :productId="item.ID"
                        :productName="item.NAME"
                        :productImage="item.PHOTO"
                        :productPrice="item.PRICES?.priceFormat"
                        :productPriceOld="
                            item.PRICES?.basePrice > item.PRICES?.price ? item.PRICES?.basePriceFormat : null
                        "
                        :link="'/product/' + item.CODE"
                        :colors="item.COLORS"
                    />
                </swiper-slide>
            </swiper>
        </div>
    </div>
</template>

<style lang="scss">
@import './slider.scss';
</style>
