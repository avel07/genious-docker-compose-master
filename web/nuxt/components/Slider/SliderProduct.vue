<script setup>
import { Swiper, SwiperSlide } from 'swiper/vue';

import 'swiper/css';
import 'swiper/css/pagination';
import 'swiper/css/free-mode';
import 'swiper/css/navigation';
import 'swiper/css/thumbs';
import 'swiper/swiper.min.css';

import { FreeMode, Pagination, Navigation, Thumbs } from 'swiper';

const props = defineProps({
    images: {
        default: []
    }
});

const thumbsSwiper = ref();

const setThumbsSwiper = (swiper) => {
    thumbsSwiper.value = swiper;
};

const modules = [FreeMode, Navigation, Thumbs, Pagination];
</script>

<template>
    <swiper
        @swiper="setThumbsSwiper"
        :slidesPerView="1"
        :navigation="true"
        :pagination="{
            type: 'fraction'
        }"
        :loop="true"
        :modules="modules"
        :thumbs="{ swiper: thumbsSwiper }"
        class="slider slider_arrow slider_view"
    >
        <swiper-slide v-for="image in props.images" :key="image">
            <div class="product-slide">
                <img :src="image" />
            </div>
        </swiper-slide>
        <!-- <swiper-slide>
            <Video className="video_slider" />
        </swiper-slide> -->
    </swiper>

    <swiper
        @swiper="setThumbsSwiper"
        :slidesPerView="'auto'"
        :freeMode="true"
        :watchSlidesProgress="true"
        :modules="modules"
        :pagination="true"
        :spaceBetween="20"
        :loop="true"
        class="slider slider_thumbs t-hidden"
    >
        <swiper-slide v-for="(image, index) in props.images" :key="index">
            <div class="product-slide">
                <img :src="image" />
            </div>
        </swiper-slide>
    </swiper>
</template>

<style lang="scss">
@import './slider.scss';

.product-slide {
    height: 100%;
    img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: 50% 50%;
    }

    &__icon {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 64px;
        height: 64px;
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: center;
        transform: translate(-50%, -50%);
    }
}
</style>
