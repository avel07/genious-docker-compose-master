<script setup lang="ts">
import { Swiper, SwiperSlide } from 'swiper/vue';

import 'swiper/css';
import 'swiper/css/pagination';
import 'swiper/swiper.min.css';

import { Pagination, Navigation } from 'swiper';

const modules = [Pagination, Navigation];

const props = defineProps({
    productId: {
        default: '',
        required: true
    },
    productName: {
        default: '',
        required: true
    },
    productImage: {
        default: ''
    },
    productPrice: {
        default: ''
    },
    productPriceOld: {
        default: ''
    },
    link: {
        default: '',
        required: true
    },
    colors: {
        type: Array<any>,
        default: []
    }
});
</script>

<template>
    <div class="product-card">
        <div class="product-card__image">
            <div class="product-card__favorite">
                <Favorite :productId="props.productId" className="button-icon_sm" />
            </div>
            <swiper
                :slidesPerView="1"
                :navigation="true"
                :loop="true"
                :modules="modules"
                :allowTouchMove="false"
                class="slider slider_arrow slider_product-card"
            >
                <swiper-slide v-for="image in productImage" :key="image">
                    <NuxtLink :to="props.link" class="product-card__link">
                        <img :src="image" alt="product" />
                    </NuxtLink>
                </swiper-slide>
            </swiper>
        </div>

        <div class="product-card__content mt-16 mt-s-12">
            <div class="product-card__info spacing fw-medium">
                <div class="product-card__name fs-s-12 uppercase">
                    <NuxtLink :to="props.link" class="link uppercase">
                        {{ props.productName }}
                    </NuxtLink>
                </div>
                <div class="mt-4 mt-s-0">
                    <span
                        v-if="props.productPriceOld"
                        class="product-card__old-price uppercase nowrap line-through fs-s-12"
                        v-html="props.productPriceOld"
                    ></span>
                    <span
                        class="product-card__price uppercase nowrap fs-s-12"
                        :class="{ 'text-red': props.productPriceOld }"
                        v-html="props.productPrice"
                    ></span>
                </div>
            </div>

            <div class="product-card__params">
                <div
                    class="product-card__color"
                    v-if="props.colors"
                    v-for="objColor in props.colors"
                    :style="{ 'background-color': objColor?.COLOR }"
                ></div>
            </div>
        </div>
    </div>
</template>

<style lang="scss">
@import './product-card.scss';
</style>
