<script setup lang="ts">
const menu = useMenu();
const footerContent = computed(() => menu.value?.footer_content?.ITEMS);
const sections = computed(() => menu.value?.sections?.ITEMS);
</script>

<template>
    <div class="header-menu uppercase spacing">
        <div class="d-show" v-if="footerContent && footerContent.length">
            <div class="pt-24">
                <NuxtLink to="/articles" class="link uppercase spacing fw-medium">Genious world</NuxtLink>
            </div>

            <div class="pt-24" v-for="item in footerContent" :key="item.NAME + item.URL">
                <NuxtLink :to="item.URL" class="link uppercase spacing fw-medium">
                    {{ item.NAME }}
                </NuxtLink>
            </div>

            <div class="pt-24">
                <NuxtLink to="/catalog" class="link uppercase spacing fw-medium">Каталог</NuxtLink>
            </div>
        </div>

        <div class="g-row g-row_t-wrap">
            <div
                class="g-col g-col_same g-col_t-50 g-col_s-100 pt-24"
                v-for="item in sections"
                :key="item.NAME + item.URL"
            >
                <div class="text-light-grey uppercase fw-medium spacing">{{ item.NAME }}</div>

                <div class="pt-24">
                    <NuxtLink :to="item.URL" class="link uppercase spacing fw-medium">все категории</NuxtLink>
                </div>

                <ul class="header-menu__list list-reset pt-24" v-if="item?.CHILD && item.CHILD.length">
                    <li v-for="childItem in item.CHILD" :key="childItem.NAME + childItem.URL">
                        <NuxtLink :to="childItem.URL" class="link uppercase fw-medium spacing">
                            {{ childItem.NAME }}
                        </NuxtLink>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<style lang="scss">
@import './header-menu.scss';
</style>
