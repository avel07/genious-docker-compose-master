<template>
    <main class="page">
        <div class="content content_little text-center" v-if="data">
            <div class="page__icon">
                <img src="/images/order-final.svg" alt="order-final" />
            </div>
            <div class="mt-32 fs-16 fs-s-14" v-parse-html="data"></div>
        </div>
    </main>
</template>

<script setup>
const { params } = useRoute();

const { data, error } = await useFetchApi(`http://localhost/api/v1/order/${params.order_id}`, {
    method: 'GET',
    server: false,
    lazy: true,
    transform: (response) => response?.data
});

definePageMeta({
    layout: 'full'
});
</script>
