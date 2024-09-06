<script lang="ts" setup>
import type { PropType } from 'vue';

type BreadCrumb = {
    title?: string;
    url?: string;
};

const props = defineProps({
    currentTitle: {
        type: String,
        required: true
    },
    sections: {
        type: Object as PropType<BreadCrumb[]>,
        required: false
    }
});

const parentsBreadCrumbs = ref(props.sections);
const currentBreadcrumbs = ref({
    title: props.currentTitle,
    url: '#'
});

const breadcrumbs = computed(() => {
    let homepage: BreadCrumb = {
        title: 'Главная',
        url: '/'
    };

    let breadcrumbsList = [homepage];

    const { fullPath } = useRoute();
    // Если это товар, то первым в предках будет каталог
    if (
        (fullPath?.includes('/product/') || fullPath?.includes('/catalog/')) &&
        !parentsBreadCrumbs.value?.find((item) => item.url == '/catalog')
    ) {
        parentsBreadCrumbs.value = [
            {
                title: 'Каталог',
                url: `/catalog`
            },
            ...(parentsBreadCrumbs.value ?? [])
        ];
    }

    // Добавляем остальные хлебные крошки
    if (parentsBreadCrumbs.value) {
        breadcrumbsList.push(...parentsBreadCrumbs.value);
    }

    // И саму страницу
    if (currentBreadcrumbs.value) {
        breadcrumbsList.push(currentBreadcrumbs.value);
    } else {
        console.warn('Заголовок в хлебные крошки');
    }

    return breadcrumbsList;
});
</script>

<template>
    <div class="breadcrumbs uppercase t-hidden spacing fw-medium" v-if="breadcrumbs">
        <template v-for="(breadcrumb, index) in breadcrumbs">
            <NuxtLink
                v-if="index != breadcrumbs.length - 1"
                :key="`breadcrumbs${index}${breadcrumb.url}`"
                :to="breadcrumb.url"
                class="breadcrumbs__link link"
            >
                {{ breadcrumb.title }}
            </NuxtLink>

            <span v-else :key="`breadcrumbs${index}${breadcrumb.url}`" class="text-light-grey">
                {{ breadcrumb.title }}
            </span>
        </template>
    </div>
</template>

<style lang="scss">
@import './breadcrumbs.scss';
</style>
