<script setup lang="ts">
const notShow = useCookie('accept_cookie_msg');
const hideMessage = () => {
    notShow.value = 'true';
}
</script>

<template>
    <div class="cookie-message bg-white" v-if="!notShow">
        <div class="fs-16 spacing fw-medium fs-s-14 uppercase">Мы используем cookie</div>
        <div class="mt-8">
            Этот сайт использует cookie для хранения данных. Продолжая использовать сайт, вы даете согласие на работу с
            этими файлами
        </div>
        <div class="mt-16">
            <div class="button button_primary button_full button_lg button_s-lg uppercase" @click="hideMessage()">
                Хорошо
            </div>
        </div>
    </div>
</template>

<style lang="scss">
@import './cookie-message.scss';
</style>
