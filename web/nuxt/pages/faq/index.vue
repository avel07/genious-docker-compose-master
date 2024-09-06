<script setup lang="ts">
const { data: faqContentResponse } = await useFetchApi('/api/v1/content/faq');
const faqTitle = computed(() => faqContentResponse.value?.data?.text?.title);
const faqDesc = computed(() => faqContentResponse.value?.data?.text?.desc);

const faqMeta = computed(() => faqContentResponse.value?.data?.meta);

const { data: faqResponse } = await useFetchApi('/api/v1/faq', {
    method: 'GET'
});

const questions = computed(() => faqResponse.value?.data?.items);

// Meta head
useHead({
    title: faqMeta.value?.metaTitle ?? faqTitle.value,
    meta: [
        {
            name: 'description',
            content: faqMeta.value?.metaDescription ?? faqTitle.value
        }
    ]
});
</script>

<template>
    <main class="page-inside">
        <div class="content">
            <Breadcrumbs :currentTitle="faqTitle" />
        </div>

        <div class="mt-40 mt-t-0 page-inside__block bg-grey fs-16 fs-s-14">
            <div class="content content_medium">
                <div class="pt-80 pb-80 pt-d-45 pb-d-45">
                    <div class="g-row g-row_wrap" v-if="questions">
                        <div class="g-col g-col_100">
                            <div class="page-inside__content">
                                <h1>{{ faqTitle ?? 'вопросы и ответы' }}</h1>
                            </div>
                        </div>

                        <div
                            class="g-col g-col_50 g-col_m-100 pt-80 pt-d-45"
                            v-for="question in questions"
                            :key="question.id"
                        >
                            <div class="page-inside__content">
                                <h3>{{ question.name }}</h3>
                                <p v-if="question?.text" v-html="question.text"></p>
                            </div>
                        </div>

                        <div class="g-col g-col_50 g-col_m-100 pt-80 pt-d-45">
                            <div class="page-inside__content">
                                <a
                                    href="mailto:support@genious.ru"
                                    class="button button_full button_dark-border uppercase button_big fs-14 fs-s-12"
                                >
                                    Если не нашли ответа на свой вопрос, напишите нам на support@genious.ru
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>
