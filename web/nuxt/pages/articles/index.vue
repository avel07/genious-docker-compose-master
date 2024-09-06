<script setup lang="ts">
// Async setup
const [{ data: articlesContentResponse }, { data: articles }] = await Promise.all([
    useFetchApi('/api/v1/content/articles', {
        transform: (response: any) => ({
            text: response?.data?.text,
            meta: response?.data?.meta
        })
    }),
    useFetchApi('/api/v1/blog', {
        method: 'GET',
        params: {
            size: 60 // TODO: Пока нет пагинации для данной страницы
        },
        transform: (response: any) => response?.data?.items
    })
]);

const articlesText = computed(() => articlesContentResponse.value?.text);
const articlesMeta = computed(() => articlesContentResponse.value?.meta);

// Meta head
useHead({
    title: articlesMeta.value?.metaTitle ?? articlesText.value?.title,
    meta: [
        {
            name: 'description',
            content: articlesMeta.value?.metaDescription ?? articlesText.value?.title
        }
    ]
});
</script>

<template>
    <main class="page">
        <div class="content content_medium">
            <div class="page-preview">
                <h1 class="fs-32 fw-medium fs-m-21 uppercase text-center">
                    {{ articlesText?.title ?? 'GENIOUS WORLD' }}
                </h1>
                <div
                    class="page-preview__text mt-56 mt-m-30 text-center fs-16 fs-s-14"
                    v-if="articlesText?.desc"
                    v-html="articlesText?.desc"
                ></div>
            </div>

            <div class="mt-24 g-row g-row_wrap" v-if="articles">
                <div
                    class="g-col g-col_50 pt-100 pt-d-45 g-col_m-100 pt-s-24"
                    v-for="article in articles"
                    :key="article.code"
                >
                    <ArticleLink :title="article.name" :imageSrc="article.image" :link="'/articles/' + article.code" />
                </div>
            </div>
        </div>
    </main>
</template>
