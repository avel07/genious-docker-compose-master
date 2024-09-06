<script setup lang="ts">
const { params } = useRoute();

const { data: articleDetail } = await useFetchApi(`/api/v1/blog/${params.code}`, {
    transform: (response: any) => response?.data
});

const prevArticle = computed(() => articleDetail.value?.previous);
const nextArticle = computed(() => articleDetail.value?.next);
const articleMeta = computed(() => articleDetail.value?.meta);

// Meta head
useHead({
    title: articleMeta.value?.metaTitle ?? articleDetail.value?.name,
    meta: [
        {
            name: 'description',
            content: articleMeta.value?.metaDescription ?? articleDetail.value?.name
        }
    ]
});
</script>

<template>
    <main class="page">
        <div class="content content_medium">
            <div class="article fs-16 fs-s-14">
                <!-- <div class="fs-32 fw-medium fs-m-21 uppercase text-center">GENIOUS</div> -->

                <h1 class="article__title pt-100 pt-d-60 pt-m-30 text-center fs-21 fs-s-16 spacing fw-medium">
                    {{ articleDetail.name }}
                </h1>

                <section class="article__section" v-if="articleDetail?.text" v-html="articleDetail.text"></section>
            </div>

            <div class="pt-100 g-row g-row_wrap pt-d-45 pt-m-0">
                <div class="g-col g-col_50 pt-100 pt-d-45 g-col_m-100">
                    <ArticleLink
                        v-if="prevArticle && prevArticle?.code"
                        :title="prevArticle.name"
                        :imageSrc="prevArticle?.image"
                        :link="'/articles/' + prevArticle.code"
                    >
                        <span class="article-link__text d-block m-hidden uppercase spacing mb-12">
                            Предыдущий проект
                        </span>
                    </ArticleLink>
                </div>

                <div class="g-col g-col_50 pt-100 pt-d-45 g-col_m-100">
                    <ArticleLink
                        v-if="nextArticle && nextArticle?.code"
                        :title="nextArticle.name"
                        :imageSrc="nextArticle?.image"
                        :link="'/articles/' + nextArticle.code"
                    >
                        <span class="article-link__text d-block m-hidden uppercase spacing mb-12 text-right">
                            следующий проект
                        </span>
                    </ArticleLink>
                </div>
            </div>
        </div>
    </main>
</template>

<style lang="scss">
.article {
    &__title {
        max-width: 390px;
        margin-left: auto;
        margin-right: auto;
    }

    &__text {
        max-width: 830px;
        margin-left: auto;
        margin-right: auto;
        p {
            &:not(:first-child) {
                margin-top: 20px;
            }
        }
    }

    &__image {
        img {
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
    }
    @media screen and (max-width: 992px) {
        &__image {
            margin-left: -20px;
            margin-right: -20px;
        }
    }

    @media screen and (max-width: 375px) {
        &__image {
            margin-left: -16px;
            margin-right: -16px;
        }
    }
}
</style>
