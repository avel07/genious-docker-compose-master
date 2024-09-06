<script setup lang="ts">
const route = useRoute();
const [{ data: mainPage }] = await Promise.all([
    useFetchApi('/api/v1/main', {
        transform: (response: any) => ({
            banner: response?.data?.banner,
            bannerMobile: response?.data?.bannerMobile,
            video: response?.data?.video,
            blocks: response?.data?.blocks,
            products: response?.data?.products?.items,
            text: response?.data?.text,
            image: response?.data?.image,
            gallery: response?.data?.gallery,
            meta: response?.data?.meta
        })
    })
]);
</script>

<template>
    <main>
        <MainBanner
            :image="mainPage.banner"
            :imageMobile="mainPage.bannerMobile"
        />

        <div v-if="mainPage.blocks.top" class="g-row g-row_simple g-row_m-wrap" v-header-inverted>
            <div class="g-col g-col_50 g-col_m-100" v-for="block in mainPage.blocks.top">
                <MainLink
                    :name="block.name"
                    :image="block.image"
                    :link="block.link"
                />
            </div>
        </div>

        <MainNews
            :text="mainPage.text.main"
            :image="mainPage.image.main.src"
        />

        <section class="section-pt section-pb">
            <SliderProductsMain
                :products="mainPage.products"
                :text="mainPage.text.products"
            />
        </section>

        <div v-header-inverted>
            <Video
                :name="mainPage.text.video"
                :video="mainPage.video"
                :className="'video_full'"
            />

            <div v-if="mainPage.blocks.bottom" class="g-row g-row_simple g-row_m-wrap">
                <div class="g-col g-col_50 g-col_m-100" v-for="block in mainPage.blocks.bottom">
                    <MainLink
                        :name="block.name"
                        :image="block.image"
                        :link="block.link"
                    />
                </div>
            </div>
        </div>

        <section class="section-pt section-pb m-hidden" v-if="mainPage.gallery">
            <SliderMain
                :gallery="mainPage.gallery"
                title="Галерея"
            />
        </section>
    </main>
</template>