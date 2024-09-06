<script setup lang="ts">
// Async setup
const { data: deliveryResponse } = await useFetchApi('/api/v1/content/delivery', {
    method: 'GET'
});

const deliveryName = computed(() => deliveryResponse.value?.data?.name);
const deliveryText = computed(() => deliveryResponse.value?.data?.text);
const deliveryImage = computed(() => deliveryResponse.value?.data?.image);
const deliveryMeta = computed(() => deliveryResponse.value?.data?.meta);

// Meta head
useHead({
    title: deliveryMeta.value?.metaTitle ?? deliveryName,
    meta: [
        {
            name: 'description',
            content: deliveryMeta.value?.metaDescription ?? ''
        }
    ]
});
</script>

<template>
    <main class="page-inside">
        <div class="content">
            <Breadcrumbs :currentTitle="deliveryName" />
        </div>

        <div class="mt-40 mt-t-0 page-inside__block bg-grey fs-16 fs-s-14">
            <div class="content content_medium">
                <div class="g-row">
                    <div class="g-col g-col_50 g-col_m-100">
                        <div class="page-inside__content pt-80 pb-80 pt-d-45 pb-d-45">
                            <h1>Оплата и доставка</h1>
                            <div v-html="deliveryText?.delivery"></div>
                        </div>
                    </div>
                    <div class="g-col g-col_50 m-hidden">
                        <div class="page-inside__image">
                            <img v-if="deliveryImage?.one" :src="deliveryImage?.one.src" alt="page-inside" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>
