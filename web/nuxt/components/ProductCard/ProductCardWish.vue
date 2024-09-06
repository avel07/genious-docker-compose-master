<script setup lang="ts">
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
    }
});
</script>

<template>
    <div class="product-card">
        <div class="product-card__image">
            <div class="product-card__favorite">
                <Favorite className="button-icon_sm" :productId="props.productId" />
            </div>
            <NuxtLink :to="props.link" class="product-card__link" v-if="props.productImage">
                <img :src="props.productImage" :alt="props.productName" />
            </NuxtLink>
        </div>

        <div class="mt-16 mt-s-12">
            <div class="product-card__info spacing fw-medium">
                <div class="product-card__name fs-s-12 uppercase">
                    <NuxtLink :to="props.link" class="link uppercase">
                        {{ productName }}
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
        </div>

        <div class="product-card__button mt-20 mt-m-12">
            <NuxtLink
                :to="props.link"
                class="button button_dark-border button_full button_lg button_s-lg fs-s-13 uppercase"
            >
                Перейти
            </NuxtLink>
        </div>
    </div>
</template>

<style lang="scss">
@import './product-card.scss';
</style>
