<script setup>
const props = defineProps({
    error: Object
});

const handleError = () => clearError({ redirect: '/' });

const isDev = process.dev;
</script>

<template>
    <div class="wrap-all full-page">
        <div class="wrap-content">
            <Header />
            <div class="content text-center">
                <div class="fs-58 fw-medium uppercase spacing">{{ props.error.statusCode }}</div>
                <div class="fs-58 fs-t-43 fs-m-29 fs-s-21 fw-medium uppercase spacing mt-32">
                    {{ props.error.statusCode == 404 ? 'Страница не&nbsp;найдена' : 'что-то пошло не&nbsp;так' }}
                </div>
                <div class="mt-36 fs-21 fs-s-14 uppercase spacing">{{ props.error.message }}</div>
                <pre v-if="isDev" class="mt-36 fs-21 fs-s-14">{{ props.error.data }}</pre>
                <div class="mt-48 page__button">
                    <a
                        href="/"
                        @click.prevent="handleError()"
                        class="button button_primary button_lg button_s-lg uppercase button_full"
                    >
                        на главную
                    </a>
                </div>
            </div>
        </div>
        <Footer />
    </div>
</template>

<style lang="scss">
@import '@@/layouts/layout.scss';
</style>
