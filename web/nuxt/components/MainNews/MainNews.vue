<script setup lang="ts">
const props = defineProps({
    text: {
        default: '',
        required: true
    },
    image: {
        default: '',
        required: true
    }
});
</script>

<template>
    <div class="main-news section-pt section-pb">
        <div class="content">
            <div class="g-row g-row_middle g-row_reverse g-row_wrap">
                <div class="g-col g-col_50 g-col_t-100">
                    <div class="main-news__image text-center">
                        <img :src="image" alt="news" />
                    </div>
                </div>

                <div class="g-col g-col_50 g-col_t-100">
                    <div class="main-news__content text-center spacing fw-medium" v-html="text"></div>
                </div>
            </div>
        </div>
    </div>
</template>

<style lang="scss">
@import './main-news.scss';
</style>
