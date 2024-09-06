<script setup lang="ts">
// Async setup
const { data: recommendationsResponse } = await useFetchApi('/api/v1/content/recommendations', {
    method: 'GET'
});

const recommendationsName = computed(() => recommendationsResponse.value?.data?.name);
const recommendationsText = computed(() => recommendationsResponse.value?.data?.text);
const recommendationsMeta = computed(() => recommendationsResponse.value?.data?.meta);

// Meta head
useHead({
    title: recommendationsMeta.value?.metaTitle ?? recommendationsName,
    meta: [
        {
            name: 'description',
            content: recommendationsMeta.value?.metaDescription ?? ''
        }
    ]
});
</script>

<template>
    <main class="page-inside">
        <div class="content">
            <Breadcrumbs :currentTitle="recommendationsName" />
        </div>

        <div class="mt-40 mt-t-0 page-inside__block bg-grey fs-16 fs-s-14">
            <div class="content content_medium">
                <div class="pt-80 pb-80 pt-d-45 pb-d-45">
                    <div class="g-row g-row_wrap">
                        <div class="g-col g-col_100">
                            <div class="page-inside__content">
                                <h1>{{ recommendationsName }}</h1>
                                <div v-html="recommendationsText?.recommendations"></div>
                            </div>
                        </div>
                    </div>
                    <div class="g-row g-row_wrap" v-html="recommendationsText?.items"></div>
                </div>
            </div>
        </div>
    </main>
</template>
