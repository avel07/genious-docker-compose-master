<template>
    <div class="content">
        <div class="uppercase fs-s-13 fw-medium spacing">
            {{ title }}
        </div>
        <div class="mt-32 mt-m-16">
            <swiper
                :slidesPerView="1"
                :navigation="true"
                :pagination="true"
                :loop="true"
                :modules="modules"
                class="slider slider_arrow slider_main"
            >
                <swiper-slide v-for="slide in gallery">
                    <img :src="slide" alt="gallery" />
                </swiper-slide>
            </swiper>
        </div>
    </div>
</template>

<script>
import { Swiper, SwiperSlide } from 'swiper/vue';

import 'swiper/css';
import 'swiper/css/pagination';
import 'swiper/swiper.min.css';

import { Pagination, Navigation } from 'swiper';

export default {
    props: {
        title: {
            type: String,
            default: ''
        },
        gallery: {
            type: Array,
            default: []
        }
    },
    components: {
        Swiper,
        SwiperSlide
    },
    setup() {
        return {
            modules: [Pagination, Navigation]
        };
    }
};
</script>

<style lang="scss">
@import './slider.scss';
</style>
