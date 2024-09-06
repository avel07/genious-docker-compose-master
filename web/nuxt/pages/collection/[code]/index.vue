<script setup lang="ts">
definePageMeta({
    layout: 'collection'
});

const { params } = useRoute();

const { data, error } = await useFetchApi(`/api/v1/collection/${params.code}`, {
    key: 'api-collection-' + params.code, // Кеш для гидрации
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

const items = ref();
const itemsIds = computed(() => {
    let result: string | number[] = [];
    if (!data?.value || data.value.length <= 0) {
        return result;
    }
    for (const layout of data.value) {
        if (layout?.blocks) {
            for (const block of layout.blocks) {
                if (block?.name == 'iblock_elements') {
                    result = result.concat(block?.element_ids);
                }
            }
        }
    }
    return result;
});

const [{ data: catalog }, { data: prices }, { data: offers }] = await Promise.all([
    useFetchApi(`/api/v1/catalog`, {
        method: 'GET',
        params: {
            filter: {
                ID: itemsIds
            },
            size: 0
        },
        key: 'api-collection-catalog' + params.code, // Кеш для гидрации
        transform: (response: any) => ({
            items: response?.data?.items,
            pagination: {
                currentPage: response?.data?.page,
                totalPages: Math.ceil(response?.data?.total / response?.data?.size),
                sizePage: response?.data?.size,
                totalItems: response?.data?.total
            }
        })
    }),
    useFetchApi(() => `/api/v1/prices`, {
        method: 'GET',
        params: {
            ids: itemsIds
        },
        key: 'api-collection-prices' + params.code, // Кеш для гидрации
        transform: (response: any) => response?.data?.prices
    }),
    useFetchApi(() => `/api/v1/offers`, {
        method: 'GET',
        params: {
            filter: {
                'CML2_LINK.VALUE': itemsIds
            },
            size: 0
        },
        key: 'api-collection-offers' + params.code, // Кеш для гидрации
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
                        self.findIndex(
                            (objDeep: any) => objDeep.COLOR === obj.COLOR && objDeep.PARENT_ID === obj.PARENT_ID
                        )
                );
            });
        }
    })
]);

// Объединяем товары с ценами и тп в единый объект
let result = catalog.value?.items;
let itemsValue: any = {};
for (const key in result) {
    if (result?.[key]) {
        const item = result[key];
        let itemId = item.ID;
        let photos = item?.PROPERTIES?.MORE_PHOTO;
        item['PHOTO'] = null;
        if (photos) {
            item['PHOTO'] = typeof photos === 'object' ? photos[0] : photos;
        }
        item['PRICES'] =
            prices.value && Object.prototype.hasOwnProperty.call(prices.value, itemId) ? prices.value[itemId] : null;
        item['COLORS'] = offers.value ? offers.value.filter((obj: any) => obj?.PARENT_ID == itemId) : null;

        itemsValue[item.ID] = item;
    }
}
items.value = itemsValue;
</script>

<template>
    <main class="section-pb">
        <template v-for="layout in data">
            <div v-if="layout?.settings.includes('isBanner')" v-header-inverted>
                <div class="main-link" v-if="layout.blocks[0]?.name == 'complex_image_text'">
                    <img :src="layout.blocks[0]?.image?.file?.ORIGIN_SRC" />
                    <span
                        class="main-link__name text-center text-white uppercase spacing fw-medium fs-32 fs-m-18"
                        v-html="layout.blocks[0]?.text?.value"
                    >
                    </span>
                </div>
            </div>

            <template v-else>
                <template v-for="block in layout.blocks">
                    <!-- Если блок элементов -->
                    <template v-if="block?.name == 'iblock_elements'">
                        <div class="content" v-if="block?.settings?.myparam1 == 'slider'">
                            <SliderProducts :ids="block?.element_ids"></SliderProducts>
                        </div>
                        <div class="content" v-else-if="block?.settings?.myparam1 == 'big_picture'">
                            <div class="g-row g-row_wrap g-row_medium g-row_s-simple mt-32 mt-m-0">
                                <template v-for="id in block.element_ids" :key="`item-${id}`">
                                    <div class="g-col g-col_50 g-col_t-100 g-col_m-100 pb-32 g-col_m-last">
                                        <ProductCard
                                            v-if="items?.[id]"
                                            :productId="items[id].ID"
                                            :productName="items[id].NAME"
                                            :productImage="items[id].PHOTO"
                                            :productPrice="items[id].PRICES?.priceFormat"
                                            :productPriceOld="
                                                items[id].PRICES?.basePrice > items[id].PRICES?.price
                                                    ? items[id].PRICES?.basePriceFormat
                                                    : null
                                            "
                                            :link="'/product/' + items[id].CODE"
                                            :colors="items[id].COLORS"
                                        />
                                    </div>
                                </template>
                            </div>
                        </div>
                        <div class="content" v-else>
                            <div class="g-row g-row_wrap g-row_medium g-row_s-simple mt-32 mt-m-0">
                                <template v-for="id in block.element_ids" :key="`item-${id}`">
                                    <div class="g-col g-col_25 g-col_t-50 pb-32" v-if="items?.[id]">
                                        <ProductCard
                                            :productId="items[id].ID"
                                            :productName="items[id].NAME"
                                            :productImage="items[id].PHOTO"
                                            :productPrice="items[id].PRICES?.priceFormat"
                                            :productPriceOld="
                                                items[id].PRICES?.basePrice > items[id].PRICES?.price
                                                    ? items[id].PRICES?.basePriceFormat
                                                    : null
                                            "
                                            :link="'/product/' + items[id].CODE"
                                            :colors="items[id].COLORS"
                                        />
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                    <!-- Если блок галерея -->
                    <template v-else-if="block?.name == 'gallery'">
                        <div class="g-row g-row_simple g-row_m-wrap mt-48 mt-m-0" v-header-inverted>
                            <div class="g-col g-col_50 g-col_m-100" v-for="image in block.images">
                                <div class="group-image">
                                    <img :src="image?.file?.ORIGIN_SRC" />
                                </div>
                            </div>
                        </div>
                    </template>
                    <!-- Если блок текст -->
                    <div v-else-if="block.name == 'text'" class="content" v-html="block?.value"></div>
                </template>
            </template>
        </template>
    </main>
</template>

<style lang="scss">
.main-link {
    height: 100vh;
    position: relative;
    display: block;
    overflow: hidden;

    img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: 50% 50%;
    }

    &__name {
        display: block;
        position: absolute;
        left: 20px;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
    }
    @media screen and (max-width: 768px) {
        height: auto;
        img {
            min-height: 100vh;
        }
    }
}

.group-image {
    height: 100%;

    img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
}
</style>
