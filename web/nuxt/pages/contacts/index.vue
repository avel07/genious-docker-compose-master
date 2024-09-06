<script setup lang="ts">
// Async setup
const { data: contactsResponse } = await useFetchApi('/api/v1/content/contacts', {
    method: 'GET'
});

const contactsName = computed(() => contactsResponse.value?.data?.name);
const contactsText = computed(() => contactsResponse.value?.data?.text.contacts);
const contactsImage = computed(() => contactsResponse.value?.data?.image?.contacts?.src);
const contactsMeta = computed(() => contactsResponse.value?.data?.meta);

// Meta head
useHead({
    title: contactsMeta.value?.metaTitle ?? contactsName,
    meta: [
        {
            name: 'description',
            content: contactsMeta.value?.metaDescription ?? ''
        }
    ]
});
</script>

<template>
    <main class="page-inside">
        <div class="content">
            <Breadcrumbs :currentTitle="contactsName" />
        </div>

        <div class="mt-40 mt-t-0 page-inside__block bg-grey fs-16 fs-s-14">
            <div class="content content_medium">
                <div class="g-row g-row_wrap">
                    <div class="g-col g-col_50 g-col_m-100">
                        <div class="page-inside__content pt-80 pb-80 pt-d-45 pb-d-45">
                            <h1>{{ contactsName }}</h1>
                            <div v-html="contactsText"></div>
                        </div>
                    </div>
                    <div class="g-col g-col_50 g-col_m-100">
                        <div class="page-inside__image">
                            <img v-if="contactsImage" :src="contactsImage" alt="map" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>
