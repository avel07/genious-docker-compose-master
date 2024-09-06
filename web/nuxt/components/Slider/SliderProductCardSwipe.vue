<template>
    <swiper
        :slidesPerView="1"
        :navigation="true"
        :loop="true"
        :modules="modules"
        class="slider slider_arrow slider_product-card"
    >
        <swiper-slide>
            <NuxtLink to="/product" class="product-card__link">
                <img src="/images/product.jpg" alt="product" />
            </NuxtLink>
        </swiper-slide>
        <swiper-slide>
            <div class="product-card__link">
                <Video className="video_catalog" />
            </div>
        </swiper-slide>
    </swiper>
</template>

<script>
import { Swiper, SwiperSlide } from 'swiper/vue';

import 'swiper/css';
import 'swiper/css/pagination';
import 'swiper/swiper.min.css';

import { Navigation } from 'swiper';

export default {
    components: {
        Swiper,
        SwiperSlide
    },
    setup() {
        return {
            modules: [Navigation]
        };
    }
};
</script>

<style lang="scss">
@import './slider.scss';
</style>
