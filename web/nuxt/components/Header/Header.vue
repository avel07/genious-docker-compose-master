<script setup>
import SvgIconUser from '~/assets/img/user.svg';
import SvgIconHeart from '~/assets/img/heart.svg';
import SvgIconCart from '~/assets/img/cart.svg';
import SvgIconLogo from '~/assets/img/logo.svg';
import SvgIconArrowDown from '~/assets/img/arrow-down.svg';
import SvgIconSearch from '~/assets/img/search.svg';

const isFull = ref(false);
const scrollPosition = ref(null);
const isOpen = ref(false);
const isSearch = ref(false);

const toogleFull = () => {
    if (isFull.value === true) {
        isFull.value = false;
        document.body.classList.remove('is-fixed');
    } else {
        isFull.value = true;
        isOpen.value = false;
        isSearch.value = false;
        document.body.classList.add('is-fixed');
    }
};

const navigateCatalogAndTogglefull = () => {
    navigateTo('/catalog');
    toogleFull();
};

// Закрываем меню при переходе на другую ссылку
const nuxtApp = useNuxtApp();
nuxtApp.hook('page:finish', () => {
    isFull.value = false;
    document.body.classList.remove('is-fixed');
});

const toggleAside = () => {
    if (isOpen.value === true) {
        isOpen.value = false;
        document.body.classList.remove('is-fixed');
    } else {
        isOpen.value = true;
        isFull.value = false;
        isSearch.value = false;
        document.body.classList.add('is-fixed');
    }
};
const closeAside = () => {
    isOpen.value = false;
    document.body.classList.remove('is-fixed');
};
const toggleSearch = () => {
    if (isSearch.value === true) {
        isSearch.value = false;
        document.body.classList.remove('is-fixed');
    } else {
        isSearch.value = true;
        isFull.value = false;
        isOpen.value = false;
        document.body.classList.add('is-fixed');
    }
};
const closeSearch = () => {
    isSearch.value = false;
    document.body.classList.remove('is-fixed');
};
const updateScroll = () => {
    scrollPosition.value = window.scrollY;
};

onMounted(() => {
    window.addEventListener('scroll', updateScroll);
});

// Отображение иконки корзины, как заполненной
const basket = useBasket();
const basketData = basket.get();
const isExistBaksetItems = computed(() => basketData.value?.items?.length);

const { auth } = useAuth();
const isAuth = computed(() => !!auth.value);
</script>

<template>
    <header class="header" v-header>
        <div
            class="header__wrapper"
            v-bind:class="{
                'is-full': isFull,
                'is-aside': isOpen,
                'is-open-search': isSearch,
                'is-up': scrollPosition > 50
            }"
        >
            <div class="content">
                <div class="g-row g-row_s-small">
                    <div class="g-col g-col_same header__item">
                        <div class="g-row g-row_middle g-row_d-small">
                            <div class="g-col d-hidden">
                                <NuxtLink to="/catalog/new" class="link nowrap uppercase spacing fw-medium"
                                    >новинки</NuxtLink
                                >
                            </div>
                            <div class="g-col d-hidden">
                                <div class="link uppercase nowrap spacing fw-medium" v-bind:class="{ 'is-active': isFull }" @click="isFull ? navigateCatalogAndTogglefull() : toogleFull()">
                                    <span class="link__text">каталог</span>
                                    <span class="link__arrow">
                                        <SvgIconArrowDown />
                                    </span>
                                </div>
                            </div>
                            <div class="g-col d-hidden">
                                <NuxtLink to="/articles" class="link uppercase nowrap spacing fw-medium"
                                    >genious world</NuxtLink
                                >
                            </div>

                            <div class="g-col d-show">
                                <div class="burger" @click="toogleFull()" v-bind:class="{ 'is-active': isFull }">
                                    <div class="burger__line"></div>
                                    <div class="burger__line"></div>
                                    <div class="burger__line"></div>
                                </div>
                            </div>
                            <div class="g-col g-col_full d-show">
                                <div class="link-icon" @click="toggleSearch()">
                                    <SvgIconSearch />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="g-col g-col_same header__item">
                        <div class="header__logo">
                            <NuxtLink to="/" class="d-block">
                                <SvgIconLogo />
                            </NuxtLink>
                        </div>
                    </div>

                    <div class="g-col g-col_same header__item g-row_end">
                        <div class="g-row g-row_middle g-row_s-small h-100">
                            <div class="header-aside g-col h-100" v-bind:class="{ 'is-top': isOpen }">
                                <div class="header-aside__layout" v-if="isOpen" @click="closeAside()"></div>

                                <div class="header-aside__menu g-row g-row_middle g-row_small">
                                    <div class="g-col d-hidden">
                                        <div
                                            class="header-search"
                                            @click="toggleSearch()"
                                            v-bind:class="{ 'is-hidden': isSearch }"
                                        >
                                            <div class="header-search__field">
                                                <Input className="header-search__input" name="search" />
                                            </div>

                                            <div class="header-search__icon">
                                                <SvgIconSearch />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="g-col">
                                        <div class="link-icon text-black" @click="toggleAside()">
                                            <SvgIconUser />
                                        </div>
                                    </div>

                                    <div class="g-col">
                                        <NuxtLink to="/wishlist" class="link-icon text-black">
                                            <SvgIconHeart />
                                        </NuxtLink>
                                    </div>

                                    <div class="g-col">
                                        <NuxtLink to="/basket" class="link-icon text-black">
                                            <SvgIconCart />
                                            <span v-show="isExistBaksetItems" class="link-icon__notice"></span>
                                        </NuxtLink>
                                    </div>
                                </div>
                                <ClientOnly>
                                    <div
                                        class="header-aside__block"
                                        v-bind:class="{ 'is-show': isOpen }"
                                        v-show="!isAuth"
                                    >
                                        <div class="header-aside__content">
                                            <HeaderForms />
                                        </div>
                                    </div>

                                    <div
                                        class="header-aside__block header-aside__block_list"
                                        v-bind:class="{ 'is-show': isOpen }"
                                        v-show="isAuth"
                                    >
                                        <div class="header-aside__content">
                                            <HeaderList @close="closeAside" />
                                        </div>
                                    </div>
                                </ClientOnly>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="isFull">
                    <HeaderMenu />
                </div>

                <div v-if="isSearch" class="mt-48 mt-m-24">
                    <Search @submit="closeSearch()" />
                </div>
            </div>
        </div>
    </header>
    <div class="header-search__layout" v-if="isSearch" @click="closeSearch()"></div>
</template>

<style lang="scss">
@import './header.scss';
@import './burger.scss';
</style>
