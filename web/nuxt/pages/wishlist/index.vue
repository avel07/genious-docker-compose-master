<script setup lang="ts">
import SvgIconBack from '~/assets/img/back.svg?component';

// получаем состояние и методы для работы с избранным
const { whishlist, addToWhishlist, removeFromWhishlist } = useWhishlist();

// Выполняем запрос только в том случае, если массив идентификаторов существует
const { data: whishlistData, error } =
    whishlist.value?.ids && whishlist.value?.ids.length
        ? await useFetchApi(() => `/api/v1/catalog`, {
              method: 'GET',
              params: {
                  filter: {
                      ID: whishlist.value?.ids
                  },
                  size: 0 // all
              },
              key: 'api-catalog-recommends', // Кеш для гидрации
              transform: (response: any) => ({
                  items: response?.data?.items
              }),
              lazy: true
          })
        : { data: ref([]), error: null };

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

// Получаем цены
const itemsIds = computed(() => whishlistData.value?.items.map((item: any) => item.ID));
const { data: prices } = await useFetchApi(`/api/v1/prices`, {
    method: 'GET',
    params: {
        ids: itemsIds
    },
    key: 'api-prices-recommends', // Кеш для гидрации
    transform: (response: any) => response?.data?.prices,
    lazy: true
});

// Объединяем товары с ценами и тп в единый объект
const items = computed(() => {
    let result = whishlistData.value?.items;
    for (const key in result) {
        if (Object.prototype.hasOwnProperty.call(result, key)) {
            const item = result[key];
            let itemId = item.ID;
            let photos = item?.PROPERTIES?.MORE_PHOTO;
            item['PHOTOS'] = null;
            if (photos) {
                item['PHOTOS'] =
                    typeof photos === 'object' ? item?.PROPERTIES?.MORE_PHOTO : [item?.PROPERTIES?.MORE_PHOTO];
            }
            item['PRICES'] =
                prices.value && Object.prototype.hasOwnProperty.call(prices.value, itemId)
                    ? prices.value[itemId]
                    : null;
        }
    }
    return result;
});

// Meta head
useHead({
    title: 'Вишлист'
});
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
                    <h1 class="fs-32 uppercase fs-m-21 fs-s-13 fw-medium">Вишлист</h1>
                </div>
            </div>

            <div class="g-row g-row_wrap g-row_medium g-row_s-small mt-12 mt-m-0" v-if="items">
                <div class="g-col g-col_25 g-col_d-33 g-col_m-50 pt-32" v-for="item in items" :key="item.ID">
                    <ProductCardWish
                        :productId="item.ID"
                        :productName="item.NAME"
                        :productImage="item.PHOTOS[0]"
                        :productPrice="item.PRICES?.priceFormat"
                        :productPriceOld="
                            item.PRICES?.basePrice > item.PRICES?.price ? item.PRICES?.basePriceFormat : null
                        "
                        :link="'/product/' + item.CODE"
                    />
                </div>
            </div>
        </div>
    </main>
</template>
