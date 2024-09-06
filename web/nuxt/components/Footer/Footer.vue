<script setup lang="ts">
import SvgIconLogo from '~/assets/img/logo.svg?component';

// Получение меню
const menu = useMenu();
const footerContent = computed(() => menu.value?.footer_content?.ITEMS);
const footerSocial = computed(() => menu.value?.footer_social?.ITEMS);
</script>

<template>
    <footer class="footer text-white bg-black">
        <div class="content">
            <div class="footer__top">
                <div class="g-row g-row_middle">
                    <div class="g-col m-hidden">
                        <div class="fs-32 uppercase fw-medium spacing">your style is</div>
                    </div>
                    <div class="g-col g-col_m-100">
                        <div class="footer__logo">
                            <SvgIconLogo />
                        </div>
                    </div>
                </div>
            </div>

            <div class="g-row g-row_between g-row_m-wrap">
                <div class="g-col g-col_m-100">
                    <div class="g-row g-row_m-wrap">
                        <div class="g-col g-col_m-100 pt-50" v-if="footerContent && footerContent.length">
                            <ul class="footer__list list-reset">
                                <li v-for="item in footerContent" :key="item.NAME + item.URL">
                                    <NuxtLink :to="item.URL" class="link uppercase spacing fw-medium">
                                        {{ item.NAME }}
                                    </NuxtLink>
                                </li>
                            </ul>
                        </div>

                        <div class="g-col g-col_m-100 pt-50" v-if="footerSocial && footerSocial.length">
                            <ul class="footer__list list-reset">
                                <li v-for="item in footerSocial" :key="item.NAME + item.URL">
                                    <NuxtLink :to="item.URL" class="link uppercase spacing fw-medium">
                                        {{ item.NAME }}
                                    </NuxtLink>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="g-col g-col_m-100 pt-50">
                    <Subscribe />
                </div>
            </div>

            <div class="footer__bottom">
                <NuxtLink to="/offer" class="link uppercase spacing fw-medium">оферта</NuxtLink>
            </div>

            <div class="mt-16 spacing fw-medium">{{ new Date().getFullYear() }}</div>
        </div>
    </footer>
</template>

<style lang="scss">
@import './footer.scss';
</style>
