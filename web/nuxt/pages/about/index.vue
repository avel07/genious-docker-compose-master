<script setup lang="ts">
// Async setup
const { data: aboutResponse } = await useFetchApi('/api/v1/content/about', {
    method: 'GET'
});

const aboutName = computed(() => aboutResponse.value?.data?.name);
const aboutText = computed(() => aboutResponse.value?.data?.text);
const aboutImage = computed(() => aboutResponse.value?.data?.image);
const aboutMeta = computed(() => aboutResponse.value?.data?.meta);

// Meta head
useHead({
    title: aboutMeta.value?.metaTitle ?? aboutName,
    meta: [
        {
            name: 'description',
            content: aboutMeta.value?.metaDescription ?? ''
        }
    ]
});
</script>

<template>
    <main class="page-inside" v-if="aboutText">
        <div class="content">
            <Breadcrumbs :currentTitle="aboutName" />
        </div>

        <div class="mt-40 mt-t-0 page-inside__block bg-black text-white fs-16 fs-s-14">
            <div class="content content_medium">
                <div class="g-row g-row_wrap">
                    <div class="g-col g-col_50 g-col_m-100">
                        <div class="page-inside__content pt-80 pb-80 pt-d-45 pb-d-45">
                            <h1>{{ aboutName }}</h1>
                            <div v-html="aboutText.about"></div>
                        </div>
                    </div>
                    <div class="g-col g-col_50 m-hidden">
                        <div class="page-inside__image">
                            <img v-if="aboutImage.one" :src="aboutImage.one.src" alt="inside-image" />
                            <img v-if="aboutImage.two" :src="aboutImage.two.src" alt="inside-image" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-inside__block page-inside__block_reverse bg-grey">
            <div class="content">
                <div class="g-row g-row_wrap g-row_reverse">
                    <div class="g-col g-col_50 g-col_m-100">
                        <div class="page-inside__content pt-80 pb-80 pt-d-45 pb-d-45" v-html="aboutText.map"></div>
                    </div>
                    <div class="g-col g-col_50 g-col_m-100">
                        <div class="page-inside__image page-inside__image_left">
                            <img v-if="aboutImage.map" :src="aboutImage.map.src" alt="map" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>
