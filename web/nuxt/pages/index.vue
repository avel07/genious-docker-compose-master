<script setup>
import SvgIconLogo from '~/assets/img/logo.svg';
</script>

<template>
    <main class="page">
        <div class="content">
            <div class="start text-center text-white">
                <div class="start__logo m-show">
                    <SvgIconLogo />
                </div>

                <div class="start__top">
                    <div class="start__preloader">
                        <img src="/images/preloader.png" :alt="preloader" />
                    </div>
                    <div class="start__title uppercase mt-16">We are coming soon</div>

                    <div class="start__text fw-medium">
                        <span>Мы работаем над новым сайтом.</span>
                        <span>Но мы всегда на связи</span>
                    </div>
                </div>

                <div class="start__bottom">
                    <div class="mt-16">
                        <a href="tel:+79030055776" class="start-link">
                            <span class="start-link__icon">
                                <svg
                                    version="1.1"
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="512"
                                    height="512"
                                    viewBox="0 0 512 512"
                                >
                                    <path
                                        fill="currentColor"
                                        d="M352 320c-32 32-32 64-64 64s-64-32-96-64-64-64-64-96 32-32 64-64-64-128-96-128-96 96-96 96c0 64 65.75 193.75 128 256s192 128 256 128c0 0 96-64 96-96s-96-128-128-96z"
                                    ></path>
                                </svg>
                            </span>
                            <span class="start-link__text">+7 903 005 57 76</span>
                        </a>
                    </div>
                    <div class="mt-16">
                        <a href="mailto:shop@genious-store.com" class="start-link">
                            <span class="start-link__icon">
                                <svg
                                    version="1.1"
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="448"
                                    height="448"
                                    viewBox="0 0 448 448"
                                >
                                    <path
                                        fill="currentColor"
                                        d="M416 376v-192c-5.25 6-11 11.5-17.25 16.5-35.75 27.5-71.75 55.5-106.5 84.5-18.75 15.75-42 35-68 35h-0.5c-26 0-49.25-19.25-68-35-34.75-29-70.75-57-106.5-84.5-6.25-5-12-10.5-17.25-16.5v192c0 4.25 3.75 8 8 8h368c4.25 0 8-3.75 8-8zM416 113.25c0-6.25 1.5-17.25-8-17.25h-368c-4.25 0-8 3.75-8 8 0 28.5 14.25 53.25 36.75 71 33.5 26.25 67 52.75 100.25 79.25 13.25 10.75 37.25 33.75 54.75 33.75h0.5c17.5 0 41.5-23 54.75-33.75 33.25-26.5 66.75-53 100.25-79.25 16.25-12.75 36.75-40.5 36.75-61.75zM448 104v272c0 22-18 40-40 40h-368c-22 0-40-18-40-40v-272c0-22 18-40 40-40h368c22 0 40 18 40 40z"
                                    ></path>
                                </svg>
                            </span>
                            <span class="start-link__text">shop@genious-store.com</span>
                        </a>
                    </div>
                    <div class="start__logo m-hidden">
                        <SvgIconLogo />
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
definePageMeta({
    layout: 'start'
});
export default {
    name: 'StartPage'
};
</script>
