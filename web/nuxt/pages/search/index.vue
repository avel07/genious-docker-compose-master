<script setup lang="ts">
import SvgIconCloseUrl from '~/assets/img/search-empty.svg?url';

const route = useRoute();

const currentTextQuery = computed(() => (typeof route.query?.query === 'string' ? route.query.query : ''));

const {
    data: catalog,
    error,
    refresh
} = await useFetchApi(() => `/api/v1/search?query=${currentTextQuery.value}`, {
    method: 'GET',
    key: 'api-search-' + currentTextQuery.value, // Кеш для гидрации
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

watch(currentTextQuery, () => {
    refresh();
});
</script>

<template>
    <main class="page">
        <div class="content">
            <div class="g-row g-row_end g-row_middle">
                <div class="g-col g-col_40 g-col_d-full text-center">
                    <Search />
                </div>

                <div class="g-col g-col_30 g-col_d-auto m-hidden">
                    <div class="g-row g-row_end">
                        <!-- <div class="g-col">
                            <Dropdown text="сортировать" className="dropdown_right">
                                <div class="pt-24">
                                    <Radio name="sorting" value="по новизне" className="radio_square">
                                        <span class="uppercase spacing fw-medium nowrap">по новизне</span>
                                    </Radio>
                                </div>

                                <div class="pt-24">
                                    <Radio name="sorting" value="по популярности" className="radio_square">
                                        <span class="uppercase spacing fw-medium nowrap">по популярности</span>
                                    </Radio>
                                </div>

                                <div class="pt-24">
                                    <Radio name="sorting" value="по цене (по убыванию)" className="radio_square">
                                        <span class="uppercase spacing fw-medium nowrap">по цене (по убыванию)</span>
                                    </Radio>
                                </div>

                                <div class="pt-24">
                                    <Radio name="sorting" value="по популярности" className="radio_square">
                                        <span class="uppercase spacing fw-medium nowrap">по цене (по возрастанию)</span>
                                    </Radio>
                                </div>
                            </Dropdown>
                        </div> -->
                    </div>
                </div>
            </div>
            <div class="section-mobile" v-if="items && items.length > 0">
                <div class="g-row g-row_wrap g-row_medium g-row_s-simple mt-12 mt-m-0">
                    <div class="g-col g-col_25 g-col_d-33 g-col_m-50 pt-32" v-for="item in items" :key="item.ID">
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
                </div>
            </div>

            <div class="section-pt section-pb text-center" v-else>
                <img :src="SvgIconCloseUrl" alt="search-empty" />

                <div class="mt-56 fs-32 fs-m-21 uppercase fw-medium spacing">ничего не найдено</div>

                <div class="mt-20 text-light-grey">По запросу «Платья зеленые» найдено 0 товаров</div>
            </div>

            <!-- <div class="section-pt">
                <div class="uppercase fs-s-13 fw-medium spacing">Может быть интересно</div>
                <div class="mt-32 mt-m-16">
                    <SliderProducts />
                </div>
            </div> -->
        </div>
    </main>
</template>
