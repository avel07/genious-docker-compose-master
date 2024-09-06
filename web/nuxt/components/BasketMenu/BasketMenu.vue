<script setup>
import SvgIconBack from '~/assets/img/back.svg';
</script>

<template>
    <div :class="'basket-menu fw-medium spacing ' + classItem">
        <div class="basket-menu__back m-show">
            <a href="#" class="link-icon link-icon_back">
                <SvgIconBack />
            </a>
        </div>

        <div class="g-row g-row_simple g-row_m-center g-row_m-small">
            <div class="basket-menu__item g-col g-col_same g-col_m-auto">
                <div class="basket-menu__link fs-s-13 fw-medium">Корзина</div>
            </div>
            <div class="basket-menu__item g-col g-col_same g-col_m-auto">
                <div class="basket-menu__link fs-s-13 fw-medium">Детали</div>
            </div>
            <div class="basket-menu__item g-col g-col_same g-col_m-auto">
                <div class="basket-menu__link fs-s-13 fw-medium">Оплата</div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        classItem: {
            type: String,
            default: ''
        }
    }
};
</script>

<style lang="scss">
@import './basket-menu.scss';
</style>
