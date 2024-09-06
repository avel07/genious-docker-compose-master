<script setup lang="ts">
import SvgIconArrowPrev from '~/assets/img/arrow-left.svg?component';
import SvgIconArrowNext from '~/assets/img/arrow-right.svg?component';

const props = defineProps({
    totalPages: Number
});

const totalPages = computed(() => props.totalPages ?? 0);
const route = useRoute();
const currentPage = computed(() => (typeof route.query?.page === 'string' ? parseInt(route.query.page) : 1));

// Если страница больше максимальной
if (currentPage.value > totalPages.value) {
    navigateTo({
        query: {
            page: totalPages.value
        }
    });
}
watch(totalPages, (totalPagesNew) => {
    if (currentPage.value > totalPagesNew) {
        navigateTo({
            query: {
                page: totalPagesNew
            }
        });
    }
});

const emit = defineEmits(['paginate']);
watch(currentPage, (newPage) => {
    window.scrollTo(0, 0);
    emit('paginate', {
        currentPage: newPage,
        totalPages: totalPages.value
    });
});

const prevPage = computed(() => {
    if (currentPage.value - 1 > 1) {
        return currentPage.value - 1;
    }

    return 1;
});

const nextPage = computed(() => {
    if (currentPage.value + 1 <= totalPages.value) {
        return currentPage.value + 1;
    }
    return totalPages.value;
});
</script>

<template>
    <div class="pagination" v-if="totalPages">
        <div class="g-row g-row_middle g-row_medium g-row_center g-row_s-small">
            <NuxtLink
                :to="{ query: { page: prevPage } }"
                :replace="false"
                class="pagination__arrow"
                :class="{ 'is-disabled': currentPage === 1 }"
            >
                <SvgIconArrowPrev />
            </NuxtLink>

            <NuxtLink v-for="count in totalPages" :to="{ query: { page: count } }" class="g-col">
                <span class="pagination__pager" :class="{ 'is-active': count === currentPage }">
                    {{ count }}
                </span>
            </NuxtLink>

            <div class="g-col">
                <NuxtLink
                    :to="{ query: { page: nextPage } }"
                    :replace="false"
                    class="pagination__arrow"
                    :class="{ 'is-disabled': currentPage === totalPages }"
                >
                    <SvgIconArrowNext />
                </NuxtLink>
            </div>
        </div>
    </div>
</template>

<style lang="scss">
@import './pagination.scss';
</style>
