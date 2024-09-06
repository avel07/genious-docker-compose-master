<script setup lang="ts">
const route = useRoute();
const { params } = route;

// Текущая страница
const currentPage = computed(() => (typeof route.query?.page === 'string' ? parseInt(route.query.page) : 1));

// Получение каталога
const filter = ref(<any>{});
const {
    data: catalog,
    error,
    pending,
    refresh: refreshCatalog
} = await useFetchApi(`/api/v1/catalog/section/${params.section_code}`, {
    method: 'GET',
    params: {
        filter: filter,
        page: currentPage,
        size: 20 // Кол-во элементов на 1 странице
    },
    key: 'api-catalog-' + params.section_code, // Кеш для гидрации
    transform: (response: any) => ({
        items: response?.data?.items,
        pagination: {
            currentPage: response?.data?.page,
            totalPages: Math.ceil(response?.data?.total / response?.data?.size),
            sizePage: response?.data?.size,
            totalItems: response?.data?.total
        },
        title: response?.data?.section?.meta?.metaPageTitle ?? response?.data?.section?.NAME,
        metaTitle: response?.data?.section?.meta?.metaTitle,
        metaDescription: response?.data?.section?.meta?.metaDescription
    })
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

// Получаем цены
const itemsIds = computed(() => catalog.value?.items.map((item: any) => item.ID));
const { data: prices } = await useFetchApi(`/api/v1/prices`, {
    method: 'GET',
    params: {
        ids: itemsIds
    },
    key: 'api-prices', // Кеш для гидрации
    transform: (response: any) => response?.data?.prices,
    lazy: true
});

// Тороговые предложения
const { data: offersColors } = await useFetchApi(`/api/v1/offers`, {
    method: 'GET',
    params: {
        filter: {
            'CML2_LINK.VALUE': itemsIds
        },
        size: 0 // all
    },
    key: 'api-offers-colors', // Кеш для гидрации
    transform: (response: any) => {
        let offers = response?.data?.items?.map((offer: any) => ({
            PARENT_ID: offer.PROPERTIES?.CML2_LINK,
            COLOR: offer.PROPERTIES?.TSVET?.HEX
        }));

        // Оставляем только уникальные цвета для каждого товара и те, которые имеют HEX
        return offers?.filter((obj: any, index: number, self: any) => {
            return (
                obj?.COLOR &&
                index ===
                    self.findIndex((objDeep: any) => objDeep.COLOR === obj.COLOR && objDeep.PARENT_ID === obj.PARENT_ID)
            );
        });
    },
    server: false, // Выполняем только на клиенте
    lazy: true
});

// Объединяем товары с ценами и тп в единый объект
const items = computed(() => {
    let result = catalog.value?.items;
    for (const key in result) {
        if (Object.prototype.hasOwnProperty.call(result, key)) {
            const item = result[key];
            let itemId = item.ID;
            let photos = item?.PROPERTIES?.MORE_PHOTO;
            item['PHOTO'] = null;
            if (photos) {
                item['PHOTO'] = typeof photos === 'object' ? photos[0] : photos;
            }
            item['PRICES'] =
                prices.value && Object.prototype.hasOwnProperty.call(prices.value, itemId)
                    ? prices.value[itemId]
                    : null;
            item['COLORS'] = offersColors.value
                ? offersColors.value.filter((obj: any) => obj?.PARENT_ID == itemId)
                : null;
        }
    }
    return result;
});

// Событие фильтра
const onFilterItems = (event: any) => {
    // Добавляем значения в фильтр и обновляем каталог
    filter.value['ID'] = event;
    refreshCatalog();
};

// Meta head
useHead({
    title: catalog.value?.meta?.metaTitle ?? catalog.value?.title,
    meta: [
        {
            name: 'description',
            content: catalog.value?.meta?.metaDescription ?? catalog.value?.title
        }
    ]
});
</script>

<template>
    <main class="page">
        <div class="content">
            <Breadcrumbs :currentTitle="catalog?.title" />
            <h1 class="fs-32 uppercase mt-16 mt-t-0 fs-m-21 fw-medium">
                {{ catalog?.meta?.metaPageTitle ?? catalog?.title ?? 'Каталог' }}
            </h1>
            <Sorting @onFilterItems="onFilterItems" />
            <div class="section-mobile">
                <div class="g-row g-row_wrap g-row_medium g-row_s-simple mt-12 mt-m-0">
                    <div class="g-col g-col_25 g-col_t-50 pt-32" v-for="item in items" :key="item.ID">
                        <ProductCard
                            :productId="item.ID"
                            :productName="item.NAME"
                            :productImage="item.PHOTO"
                            :productPrice="item.PRICES?.priceFormat"
                            :productPriceOld="
                                item.PRICES?.basePrice > item.PRICES?.price ? item.PRICES?.basePriceFormat : null
                            "
                            :link="'/product/' + item.CODE"
                            :colors="item.COLORS"
                        />
                    </div>
                    <!-- <div class="g-col g-col_50 g-col_t-100 g-col_m-100 pt-32">
                        <ProductCard productName="Чёрное платье" productPrice="12 000 р" productImage="product-large" />
                    </div> -->
                </div>
            </div>
            <div class="section-pt" v-if="catalog?.pagination && items?.length">
                <Pagination :total-pages="catalog.pagination?.totalPages" />
            </div>
        </div>
    </main>
</template>
