<script setup lang="ts">
// Async setup
const { data: returnResponse } = await useFetchApi('/api/v1/content/return', {
    method: 'GET'
});

const returnName = computed(() => returnResponse.value?.data?.name);
const returnText = computed(() => returnResponse.value?.data?.text);
const returnImage = computed(() => returnResponse.value?.data?.image);
const returnMeta = computed(() => returnResponse.value?.data?.meta);

// Meta head
useHead({
    title: returnMeta.value?.metaTitle ?? returnName,
    meta: [
        {
            name: 'description',
            content: returnMeta.value?.metaDescription ?? ''
        }
    ]
});
</script>

<template>
    <main class="page-inside">
        <div class="content">
            <Breadcrumbs :currentTitle="returnName" />
        </div>

        <div class="mt-40 mt-t-0 page-inside__block bg-grey fs-16 fs-s-14">
            <div class="content content_medium">
                <div class="g-row">
                    <div class="g-col g-col_50 g-col_m-100">
                        <div class="page-inside__content pt-80 pb-80 pt-d-45 pb-d-45">
                            <h1>{{ returnName }}</h1>
                            <div v-html="returnText?.return"></div>
                        </div>
                    </div>
                    <div class="g-col g-col_50 m-hidden">
                        <div class="page-inside__image">
                            <img v-if="returnImage?.return" :src="returnImage?.return.src" alt="page-inside" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>
