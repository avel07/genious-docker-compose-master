<script setup>
import SvgIconClose from '~/assets/img/close.svg';
</script>

<template>
    <div class="text-right text-m-left">
        <div class="uppercase fs-12 fs-s-12 text-light-grey link link_underline fw-medium spacing" @click="openPopup()">
            таблица размеров
        </div>
    </div>

    <div class="popup" v-if="isOpen">
        <div class="popup__layout" @click="closePopup()"></div>
        <div class="popup__wrapper popup__wrapper_bottom">
            <div class="popup__block bg-white">
                <div class="popup__close" @click="closePopup()">
                    <SvgIconClose />
                </div>

                <div class="fs-21 uppercase fw-medium spacing fs-m-18">Таблица размеров</div>

                <div class="mt-24 fs-16 fs-s-14">На фотографии модель с параметрами 187см и одета в размер 38</div>

                <div class="mt-56 mt-m-30 fs-16 fs-s-14">
                    <TableSizes />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            isOpen: false
        };
    },
    methods: {
        openPopup() {
            this.isOpen = true;
        },
        closePopup() {
            this.isOpen = false;
        }
    }
};
</script>

<style lang="scss">
@import './popup.scss';
</style>
