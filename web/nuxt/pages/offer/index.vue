<script setup lang="ts">
// Async setup
const { data: offerResponse } = await useFetchApi('/api/v1/content/offer', {
    method: 'GET'
});

const offerName = computed(() => offerResponse.value?.data?.name);
const offerText = computed(() => offerResponse.value?.data?.text);
const offerMeta = computed(() => offerResponse.value?.data?.meta);

// Meta head
useHead({
    title: offerMeta.value?.metaTitle ?? offerName,
    meta: [
        {
            name: 'description',
            content: offerMeta.value?.metaDescription ?? ''
        }
    ]
});
</script>

<template>
    <main class="page-inside">
        <div class="content">
            <Breadcrumbs :currentTitle="offerName" />
        </div>

        <div class="mt-40 mt-t-0 page-inside__block bg-grey fs-16 fs-s-14">
            <div class="content content_medium">
                <div class="pt-80 pb-80 pt-d-45 pb-d-45">
                    <div class="g-row g-row_wrap">
                        <div class="g-col g-col_100">
                            <h1>{{ offerName }}</h1>
                        </div>
                    </div>
                    <div class="g-row g-row_wrap" v-html="offerText?.offer"></div>
                </div>
            </div>
        </div>
    </main>
</template>
