<script setup lang="ts">
import SvgIconBack from '~/assets/img/back.svg?component';
// Async setup
const { data: orders, error } = await useFetchApi('/api/v1/orders/user', {
    server: false,
    transform: (response: any) => response?.data
});

// Если ошибка от API товаров
if (isRef(error) && error.value) {
    let fetchError = error.value;
    throw createError({
        statusCode: fetchError.statusCode,
        statusMessage: fetchError.statusText,
        data: fetchError,
        fatal: true
    });
}
</script>

<template>
    <main class="page">
        <div class="content content_small">
            <div class="g-row g-row_middle">
                <div class="g-col m-show">
                    <a href="#" class="link-icon">
                        <SvgIconBack />
                    </a>
                </div>

                <div class="g-col">
                    <h1 class="fs-32 uppercase fs-m-21 fs-s-13 fw-medium">заказы</h1>
                </div>
            </div>

            <div class="mt-24" v-if="orders && orders.length > 0">
                <OrderItem
                    v-for="order in orders"
                    :key="order.ACCOUNT_NUMBER"
                    :number="order.ACCOUNT_NUMBER"
                    :status="order.STATUS_NAME"
                    :dataStatus="order.DATE_STATUS"
                    :dataStart="order.DATE_INSERT"
                    :payment="order.PAYMENT_NAME"
                    :delivery="order.SHIPMENT_NAME"
                    :price="order.PRICE"
                    :products="order.PRODUCTS_DATA"
                />
            </div>
        </div>
    </main>
</template>
