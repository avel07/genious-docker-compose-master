<template>
    <div class="product-card">
        <div class="product-card__image">
            <div class="product-card__favorite">
                <Favorite className="button-icon_sm" />
            </div>
            <SliderProductCardSwipe />
        </div>

        <div class="product-card__content mt-16 mt-s-12">
            <div class="product-card__info spacing fw-medium">
                <div class="product-card__name fs-s-12 uppercase">
                    <NuxtLink to="/product" class="link uppercase">
                        {{ productName }}
                    </NuxtLink>
                </div>
                <div class="mt-4 mt-s-0">
                    <span class="product-card__old-price uppercase fs-s-12 nowrap line-through">{{
                        productPriceOld
                    }}</span>
                    <span :class="'product-card__price uppercase fs-s-12 nowrap ' + productPriceNewClass">{{
                        productPrice
                    }}</span>
                </div>
            </div>

            <div class="product-card__params">
                <div class="product-card__color" style="background-color: #e19b5b"></div>
                <div class="product-card__color" style="background-color: #425187"></div>
                <div class="product-card__color" style="background-color: #e19b5b"></div>
                <div class="product-card__color" style="background-color: #425187"></div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        productName: {
            type: String,
            default: ''
        },
        productPrice: {
            type: String,
            default: ''
        },
        productPriceOld: {
            type: String,
            default: ''
        }
    }
};
</script>

<style lang="scss">
@import './product-card.scss';
</style>
