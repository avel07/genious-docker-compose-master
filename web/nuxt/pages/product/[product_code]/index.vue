<script setup lang="ts">
const { params } = useRoute();

// Запрашиваем инфу о товаре и его торговых предложениях
const [{ data: data, error: error }, { data: offers, error: offersError }] = await Promise.all([
    useFetchApi(`/api/v1/catalog/product/${params.product_code}`, {
        method: 'GET',
        params: {},
        key: `api-detail-page-${params.product_code}`, // Кеш для гидрации
        transform: (response: any) => {
            let data = response?.data;
            return {
                article: data?.PROPERTIES?.CML2_ARTICLE,
                ID: data.ID,
                PROPERTIES: data?.PROPERTIES,
                descPreview: data?.PREVIEW_TEXT,
                title: data?.meta?.metaPageTitle ?? data?.NAME,
                metaTitle: data?.meta?.metaTitle,
                metaDescription: data?.meta?.metaDescription,
                recommends: data?.PROPERTIES?.RECOMMENDS,
                SECTION: data?.SECTION
            };
        }
    }),
    useFetchApi(`/api/v1/offers`, {
        method: 'GET',
        params: {
            filter: {
                'CML2_LINK.ELEMENT.CODE': params.product_code
            },
            size: 0 // all
        },
        key: 'api-offers-colors', // Кеш для гидрации
        transform: (response: any) => response?.data?.items
    })
]);

// Если ошибка от API товара
if (isRef(error) && error.value) {
    let fetchError = error.value;
    throw createError({
        statusCode: fetchError.statusCode,
        statusMessage: fetchError.statusText,
        data: fetchError,
        fatal: true
    });
}

if (isRef(offersError) && offersError.value) {
    let fetchError = offersError.value;
    throw createError({
        statusCode: fetchError.statusCode,
        statusMessage: fetchError.statusText,
        data: fetchError,
        fatal: true
    });
}

// Получаем цены на торговые предложения
const { data: prices } = await useFetchApi(`/api/v1/prices`, {
    method: 'GET',
    params: {
        ids: offers.value && offers.value.length ? offers.value.map((item: any) => item.ID) : [data.value?.ID]
    },
    key: 'api-detail-prices', // Кеш для гидрации
    transform: (response: any) => response?.data?.prices
});

const currentColor = ref();
const currentSize = ref();
const currentLength = ref();

// Функция выбирает уникальные значения свойства по всем торговым предложениям и сортирует
const uniquePropsOffers = (propertyCode: any) => {
    return offers.value
        ?.filter((obj: any, index: number, self: any) => {
            return (
                obj?.PROPERTIES?.hasOwnProperty(propertyCode) &&
                obj.PROPERTIES[propertyCode]?.XML_ID &&
                index ===
                    self.findIndex(
                        (objDeep: any) =>
                            objDeep.PROPERTIES[propertyCode].XML_ID === obj.PROPERTIES[propertyCode].XML_ID
                    )
            );
        })
        .sort(
            (itemOne: any, itemTwo: any) =>
                itemOne.PROPERTIES[propertyCode].SORT - itemTwo.PROPERTIES[propertyCode].SORT
        );
};

// Получаем уникальные цвета
const colors = computed(() => {
    return uniquePropsOffers('TSVET')?.map((obj: any) => {
        return obj?.PROPERTIES?.TSVET;
    });
});
currentColor.value = colors.value[0]; // По умолчанию первый цвет активный
const setColor = (color: any) => {
    currentColor.value = color;
};

// Получаем уникальные размеры в зависимости от выбранного цвета
const sizes = computed(() => {
    return uniquePropsOffers('RAZMER')?.map((obj: any) => {
        let element = obj?.PROPERTIES?.RAZMER;
        element.disabled = true;
        for (const key in offers.value) {
            if (Object.prototype.hasOwnProperty.call(offers.value, key)) {
                const offer = offers.value[key];
                if (
                    offer?.PROPERTIES?.RAZMER?.VALUE == element?.VALUE &&
                    offer?.PROPERTIES?.TSVET?.HEX === currentColor.value?.HEX
                ) {
                    element.disabled = false;
                }
            }
        }
        return element;
    });
});
currentSize.value = sizes.value.filter((obj: any) => obj.disabled === false)[0] ?? null; // Выбираем первый размер который доступен по цвету
const setSize = (size: any) => {
    if (!size.disabled) {
        currentSize.value = size;
    }
};

// Получаем уникальные размеры в зависимости от выбранного цвета
const length = computed(() => {
    return uniquePropsOffers('DLINA')?.map((obj: any) => {
        let element = obj?.PROPERTIES?.DLINA;
        element.disabled = true;
        for (const key in offers.value) {
            if (Object.prototype.hasOwnProperty.call(offers.value, key)) {
                const offer = offers.value[key];
                if (
                    offer?.PROPERTIES?.DLINA?.VALUE == element?.VALUE &&
                    offer?.PROPERTIES?.TSVET?.HEX === currentColor.value?.HEX &&
                    offer?.PROPERTIES?.RAZMER?.VALUE === currentSize.value?.VALUE
                ) {
                    element.disabled = false;
                }
            }
        }
        return element;
    });
});
currentLength.value = length.value.filter((obj: any) => obj.disabled === false)[0]; // Выбираем первый размер который доступен по цвету
const setLength = (len: any) => {
    if (!len.disabled) {
        currentLength.value = len;
    }
};

// Текущее ТП
const currentOffer = computed(() => {
    // Если это простой товар
    if (!offers.value || !offers.value.length) {
        let product = data.value;
        product['price'] = prices.value[product.ID]; // Цену за одно проставляем
        return product;
    }

    // Если нет таких вариантов по размеру
    const availableSizes = sizes.value.filter((obj: any) => obj.disabled === false);
    if (!availableSizes.filter((obj: any) => obj?.XML_ID === currentSize.value?.XML_ID).length) {
        currentSize.value = availableSizes[0];
    }

    // Если нет таких вариантов по длине
    if (length.value && length.value.length) {
        const availableLengths = length.value.filter((obj: any) => obj.disabled === false);
        if (!availableLengths.filter((obj: any) => obj?.XML_ID === currentLength.value?.XML_ID).length) {
            currentLength.value = availableLengths[0];
        }
    }

    // Получаем текущее ТП
    for (const key in offers.value) {
        if (Object.prototype.hasOwnProperty.call(offers.value, key)) {
            const offer = offers.value[key];
            if (offer?.PROPERTIES?.RAZMER?.VALUE !== currentSize.value?.VALUE) {
                continue;
            }
            if (currentColor.value && offer?.PROPERTIES?.TSVET?.HEX !== currentColor.value?.HEX) {
                continue;
            }
            if (currentLength.value && offer?.PROPERTIES?.DLINA?.VALUE !== currentLength.value?.VALUE) {
                continue;
            }
            // Итоговое торговое предложение
            offer['price'] = prices.value[offer.ID]; // Цену за одно проставляем
            return offer;
        }
    }
});

const basket = useBasket();
const basketData = basket.get();

// Проверяем есть ли текущее ТП в корзине
const isCurrentOfferAddedToBasket = computed(() => {
    if (basketData.value && basketData.value.items.length) {
        return basketData.value.items.find((item) => item.PRODUCT_ID == currentOffer.value.ID);
    }
    return false;
});

const addToBasket = async (offerId: string | number) => {
    await basket.add(offerId);
    // TODO: Действие после добавления в корзину
};

// Meta head
useHead({
    title: data.value?.metaTitle ?? data.value?.title,
    meta: [
        {
            name: 'description',
            content: data.value?.metaDescription ?? data.value?.title
        }
    ]
});

// Хлебные крошки
// Добавляем разделы в цепочку
const sectionBreadCrumbs = ref();
if (data.value?.SECTION?.NAME) {
    sectionBreadCrumbs.value = [
        {
            title: data.value?.SECTION?.NAME,
            url: `/catalog/${data.value?.SECTION?.CODE}`
        }
    ];
}
</script>

<template>
    <main class="page page_pt-t-0">
        <div class="content">
            <Breadcrumbs :currentTitle="data?.title" :sections="sectionBreadCrumbs" />

            <div class="product g-row g-row_between g-row_wrap mt-40 mt-t-0">
                <div class="g-col g-col_50 g-col_t-100">
                    <div class="product__slider" v-if="currentOffer?.PROPERTIES?.MORE_PHOTO?.length">
                        <div class="product__favorite">
                            <!-- TODO: вишлист -->
                            <Favorite :productId="data.ID" className="bg-white button-icon_lg" />
                        </div>

                        <div class="product__link">
                            <Gallery :images="currentOffer.PROPERTIES?.MORE_PHOTO" />
                        </div>

                        <SliderProduct :images="currentOffer.PROPERTIES?.MORE_PHOTO" />
                    </div>
                </div>

                <div class="g-col g-col_50 g-col_t-100">
                    <div class="product__info">
                        <div class="g-row mt-t-36 mt-m-16">
                            <div class="g-col g-col_full">
                                <div class="fs-21 fs-m-18 fs-s-13 uppercase fw-medium spacing">{{ data.title }}</div>
                            </div>

                            <div class="g-col">
                                <div
                                    class="fs-21 fs-m-18 fs-s-13 uppercase nowrap fw-medium spacing"
                                    v-if="currentOffer.price?.priceFormat"
                                    v-html="currentOffer.price?.priceFormat"
                                ></div>
                            </div>
                        </div>

                        <div
                            class="mt-8 mt-m-4 uppercase fs-12 text-light-grey fw-medium spacing fs-s-12"
                            v-if="data?.article"
                        >
                            АРТ — {{ data.article }}
                        </div>

                        <div
                            class="mt-24 fs-16 fs-s-14 product__order1"
                            v-if="data?.descPreview"
                            v-html="data.descPreview"
                        ></div>

                        <div class="mt-45 mt-m-24" v-if="colors && colors.length">
                            <div class="uppercase fs-s-13 text-light-grey fw-medium spacing">цвет</div>
                            <div class="mt-8 g-row g-row_medium g-row_s-small">
                                <div class="g-col pt-8" v-for="color in colors" :key="color.XML_ID">
                                    <a
                                        href="javascript:void(0)"
                                        @click.prevent="setColor(color)"
                                        class="product__color"
                                        :class="{ 'is-active': currentColor.XML_ID === color.XML_ID }"
                                        :style="{ 'background-color': color.HEX }"
                                    ></a>
                                </div>
                            </div>
                        </div>

                        <div class="mt-45 mt-m-24 g-row g-row_medium g-row_wrap" v-if="sizes && sizes.length">
                            <div class="g-col g-col_50 g-col_m-100">
                                <div class="uppercase fs-s-13 text-light-grey fw-medium spacing">Размеры eu</div>
                            </div>
                            <div class="g-col g-col_50 g-col_m-100 product__m-order1 mt-m-16">
                                <PopupSizes />
                            </div>

                            <div class="g-col g-col_100 g-col_m-100">
                                <div class="mt-8 g-row g-row_wrap g-row_medium fs-s-10">
                                    <div class="g-col pt-8" v-for="size in sizes" :key="size.XML_ID">
                                        <a
                                            href="javascript:void(0)"
                                            @click.prevent="setSize(size)"
                                            class="product__size text-black link fs-16 fs-s-14"
                                            :class="{
                                                'line-through': size.disabled,
                                                'is-active': currentSize.XML_ID === size.XML_ID
                                            }"
                                        >
                                            {{ size.VALUE }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-45 mt-m-24 g-row g-row_medium g-row_wrap" v-if="length && length.length">
                            <div class="g-col g-col_50 g-col_m-100">
                                <div class="uppercase fs-s-13 text-light-grey fw-medium spacing">Длина</div>
                            </div>
                            <div class="g-col g-col_100 g-col_m-100">
                                <div class="mt-8 g-row g-row_wrap g-row_medium fs-s-10">
                                    <div class="g-col pt-8" v-for="len in length" :key="len.XML_ID">
                                        <a
                                            href="javascript:void(0)"
                                            @click.prevent="setLength(len)"
                                            class="product__size text-black link fs-16 fs-s-14"
                                            :class="{
                                                'line-through': len.disabled,
                                                'is-active': currentLength.XML_ID === len.XML_ID
                                            }"
                                        >
                                            {{ len.VALUE }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-45">
                            <button
                                @click.prevent="
                                    !isCurrentOfferAddedToBasket ? addToBasket(currentOffer.ID) : navigateTo('/basket')
                                "
                                class="button button_lg button_full uppercase fs-s-13 button_s-lg"
                                :class="{
                                    button_primary: !isCurrentOfferAddedToBasket,
                                    'button_dark-border': isCurrentOfferAddedToBasket
                                }"
                            >
                                {{ isCurrentOfferAddedToBasket ? 'В корзине' : 'Добавить в корзину' }}
                            </button>
                        </div>

                        <div class="mt-12">
                            <!-- TODO: форма -->
                            <PopupConsultation />
                        </div>

                        <div class="mt-56 mt-t-36 product__order2">
                            <ProductTabs />
                        </div>
                    </div>
                </div>
            </div>

            <ClientFallback v-if="data?.recommends?.length">
                <div class="section-pt">
                    <div class="uppercase fs-s-13 fw-medium spacing">стилизовано с...</div>
                    <div class="mt-32 mt-m-16">
                        <SliderProducts :ids="data.recommends" />
                    </div>
                </div>
            </ClientFallback>

            <!--TODO: Bigdata хотели сделать-->
            <!-- <div class="section-pt">
                <div class="uppercase fs-s-13 fw-medium spacing">Смотреть также</div>
                <div class="mt-32 mt-m-16">
                    <SliderProducts />
                </div>
            </div> -->
        </div>
    </main>
</template>

<style lang="scss">
.product {
    padding-bottom: 40px;
    &__slider {
        width: 100%;
        max-width: 692px;
        position: relative;
    }
    &__info {
        width: 100%;
        max-width: 548px;
        margin-left: auto;
        margin-right: auto;
    }
    &__color {
        width: 40px;
        height: 40px;
        display: block;
        border-radius: 50%;
        box-shadow: 0px 0px 0px 1px #dfdddd;
        &.is-active {
            box-shadow: 0px 0px 0px 4px rgba(0, 0, 0, 0.08);
        }
    }
    &__size {
        &.is-active {
            text-decoration: underline;
        }
    }
    &__favorite {
        position: absolute;
        top: 16px;
        right: 72px;
        z-index: 5;
    }
    &__link {
        position: absolute;
        top: 16px;
        right: 20px;
        z-index: 5;
    }

    @media screen and (max-width: 992px) {
        &__slider {
            margin-left: auto;
            margin-right: auto;
        }
        &__info {
            width: 100%;
            max-width: none;
            display: flex;
            flex-direction: column;
        }
        &__favorite {
            top: auto;
            bottom: 12px;
        }
        &__link {
            top: auto;
            bottom: 12px;
        }

        &__order1 {
            order: 1;
        }
        &__order2 {
            order: 2;
        }
    }

    @media screen and (max-width: 768px) {
        &__slider {
            width: auto;
            max-width: none;
            margin-left: -20px;
            margin-right: -20px;
        }
        &__m-order1 {
            order: 1;
        }
    }

    @media screen and (max-width: 576px) {
        &__color {
            width: 28px;
            height: 28px;
        }
        &__favorite {
            right: 52px;
        }
    }

    @media screen and (max-width: 375px) {
        &__slider {
            margin-left: -16px;
            margin-right: -16px;
        }
        &__favorite {
            right: 48px;
        }
        &__link {
            right: 16px;
        }
    }
}
</style>
