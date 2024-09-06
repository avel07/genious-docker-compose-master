<script setup>
import SvgIconArrowNext from '~/assets/img/arrow-next.svg?component';
import _debounce from 'lodash.debounce';

const loading = ref(true); // Индикатор загрузки
const orderData = ref({}); // Информация калькуляции заказа
const currentDelivery = ref(null); // Текущая доставка
const currentPayment = ref(null); // Текущая платежка

const country = ref('Россия'); // Дефолтная страна
const comment = ref(''); // Комментарий
// Свойства заказа
const property = ref({});

// Итоговая стоимость, скидки и т.п.
const total = computed(() => orderData.value?.TOTAL);

// Список доставок
const deliveryList = computed(() => {
    if (orderData.value?.DELIVERY) {
        // Активная доставка
        for (const key in orderData.value.DELIVERY) {
            if (orderData.value.DELIVERY?.[key]) {
                const delivery = orderData.value.DELIVERY[key];
                if (delivery?.CHECKED && delivery.CHECKED == 'Y') {
                    currentDelivery.value = delivery.ID;
                }
            }
        }
        return orderData.value.DELIVERY;
    }
});

// Список платежек
const paymentsList = computed(() => {
    if (orderData.value?.PAY_SYSTEM) {
        // Активная оплата
        for (const key in orderData.value.PAY_SYSTEM) {
            if (orderData.value.PAY_SYSTEM?.[key]) {
                const payment = orderData.value.PAY_SYSTEM[key];
                if (payment?.CHECKED && payment.CHECKED == 'Y') {
                    currentPayment.value = payment.ID;
                }
            }
        }
        return orderData.value.PAY_SYSTEM;
    }
});

// Функция реализующая запрос к API для калькуляции и создания заказа
const request = async (requestData = {}, createOrder = false) => {
    loading.value = true;
    let defaultRequest = {
        via_ajax: 'Y',
        is_ajax_post: 'Y',
        location_type: 'code',
        SITE_ID: 's1',
        PERSON_TYPE: 1
    };

    requestData = {
        ...defaultRequest,
        ...requestData
    };

    // Доп парметры
    let otherOpts = {};

    if (createOrder) {
        requestData['soa-action'] = 'saveOrderAjax';
        otherOpts['transform'] = (response) => response?.order;
    } else {
        requestData['soa-action'] = 'refreshOrderAjax';
        otherOpts['transform'] = (response) => response?.order;
    }

    if (orderData.value) {
        // ID доставки и платежки
        requestData['DELIVERY_ID'] = currentDelivery.value;
        requestData['PAY_SYSTEM_ID'] = currentPayment.value;
        requestData['ORDER_DESCRIPTION'] = comment.value;

        // Свойства в формат
        if (property.value && orderData.value?.ORDER_PROP) {
            let propertiesData = orderData.value.ORDER_PROP?.properties ? orderData.value.ORDER_PROP.properties : null;
            if (propertiesData) {
                for (const id in property.value) {
                    if (Object.hasOwnProperty.call(property.value, id)) {
                        const value = property.value?.[id];
                        let propertyReal = propertiesData.find((o) => o.CODE === id);
                        if (propertyReal) {
                            requestData['ORDER_PROP_' + propertyReal.ID] = value;

                            if (propertyReal.IS_LOCATION == 'Y') {
                                requestData['RECENT_DELIVERY_VALUE'] = value;
                            }
                        }
                    }
                }
            }
        }
    }

    // Отправляем запрос
    const { data, error } = await useFetchApi(() => `/api/v1/order`, {
        method: 'POST',
        body: requestData,
        server: false,
        lazy: true,
        ...otherOpts
    });

    nextTick(() => {
        loading.value = false;
    });

    if (isRef(error) && error.value) {
        console.error('TODO');
    }

    return data;
};

// Функция перерасчета заказа
const calculate = async () => {
    const data = await request({}, false);
    orderData.value = data.value;
    // Устанавливаем свойства в формат
    if (orderData.value) {
        if (Object.hasOwnProperty.call(orderData.value, 'ORDER_PROP')) {
            let properties = orderData.value.ORDER_PROP.properties;
            for (const key in properties) {
                if (Object.hasOwnProperty.call(properties, key)) {
                    const propertyValue = properties[key];
                    property.value[propertyValue.CODE] = propertyValue.VALUE[0];
                }
            }

            // "Привязка свойств" к доставкам/оплатам (удаление недоступных полей)
            if (properties.length !== property.value.length) {
                let allowPropCodes = properties.map((item) => item['CODE']);
                for (const code in self.property) {
                    if (allowPropCodes.indexOf(code) === -1) {
                        delete property.value[code];
                    }
                }
            }
        }
    }
};

onMounted(async () => {
    await nextTick(async () => {
        await calculate();
    });
});

// Корзина
const basket = useBasket();
const basketData = basket.get();
const basketItems = computed(() => {
    return basketData.value?.items;
});

// Функция оформления заказа
const errorsCreated = ref([]);
const createOrder = async () => {
    const data = await request({}, true);
    if (data.value?.ID) {
        basket.load();
        navigateTo(`/order/${data.value.ID}`);
    } else {
        for (const key in data.value.ERROR) {
            if (data.value.ERROR?.[key]) {
                const errorBlock = data.value.ERROR[key];
                for (const iterator of errorBlock) {
                    errorsCreated.value.push(iterator);
                }
                setTimeout(() => {
                    errorsCreated.value = [];
                }, 5000);
            }
        }
    }
};

// При изменении переменных будет вызван перерасчёт
const watchCalculatedFields = computed(() => [
    property.value['STREET'],
    property.value['HOUSE'],
    property.value['LOCATION'],
    property.value['CITY'],
    currentDelivery.value,
    currentPayment.value
]);

// Перерасчет при изменении полей
watch(
    watchCalculatedFields,
    _debounce((newValue, oldValue) => {
        // При первом расчйте (подстановки свойств, не считаем)
        if (!oldValue.every((value) => !value || value === undefined)) {
            calculate();
        }
    }, 500)
);

// Нужен для модулей (Сдек, почта россии)
if (process.client) {
    window.BX = {
        Sale: {
            OrderAjaxComponent: {
                sendRequest: async () => await request({}, false)
            }
        }
    };
}
</script>

<template>
    <main class="page">
        <div class="content content_small">
            <BasketMenu classItem="is-step-second" />
            <div :class="{ 'loading-wait': !!loading }" class="g-row g-row_reverse mt-56 mt-m-30 g-row_t-wrap">
                <div class="g-col g-col_t-100">
                    <div class="basket-aside">
                        <BasketCardFinal v-for="item in basketItems" :key="item.PRODUCT_ID" :item="item" />

                        <div class="pt-12 fw-medium" v-if="total">
                            <!-- 
                            <div class="mt-20 g-row g-row_between">
                                <div class="g-col">Доставка</div>
                                <div class="g-col">Быстрая (1-2 дня)</div>
                            </div> 
                            -->

                            <div class="mt-20 g-row g-row_between">
                                <div class="g-col">Цена доставки</div>
                                <div class="g-col">
                                    <div class="nowrap spacing" v-html="total.DELIVERY_PRICE_FORMATED"></div>
                                </div>
                            </div>

                            <!-- 
                            <div class="mt-20 g-row g-row_between">
                                <div class="g-col">Скидка по промокоду</div>
                                <div class="g-col">
                                    <div class="nowrap spacing">-10%</div>
                                </div>
                            </div> 
                            -->

                            <div class="mt-36 g-row g-row_between">
                                <div class="g-col">Конечная стоимость</div>
                                <div class="g-col">
                                    <div class="nowrap spacing" v-html="total.ORDER_TOTAL_PRICE_FORMATED"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="g-col g-col_full g-col_t-100 mt-t-48">
                    <div class="order-form">
                        <form class="form">
                            <h3 class="fs-16 uppercase fw-medium">получатель</h3>

                            <div class="form__field mt-36 mt-m-30">
                                <Input placeholderText="Имя" v-model="property['NAME']" />
                            </div>

                            <div class="form__field mt-36 mt-m-30">
                                <Input placeholderText="фамилия" v-model="property['LAST_NAME']" />
                            </div>

                            <div class="form__field mt-36 mt-m-30">
                                <Input placeholderText="Телефон" type="tel" v-model="property['PHONE']" />
                            </div>

                            <div class="form__field mt-36 mt-m-30">
                                <Input placeholderText="Электронная почта" type="email" v-model="property['EMAIL']" />
                            </div>

                            <h3 class="fs-16 uppercase fw-medium mt-70 mt-t-44" v-show="deliveryList">
                                Способ получения
                            </h3>
                            {{ orderData.DELIVERY }}
                            <div class="order-form__border mt-32" v-show="deliveryList">
                                <Radio
                                    v-for="delivery in deliveryList"
                                    :key="delivery.ID"
                                    v-model="currentDelivery"
                                    :option="delivery.ID"
                                >
                                    <span class="g-row g-row_between g-row_s-small">
                                        <span class="g-col d-block">
                                            <span
                                                class="d-block uppercase fw-medium spacing"
                                                v-html="delivery.NAME"
                                            ></span>
                                            <span class="d-block text-light-grey" v-html="delivery.DESCRIPTION"></span>
                                        </span>
                                        <span class="g-col d-block" v-if="delivery?.PRICE_FORMATED">
                                            <span
                                                class="d-block nowrap fw-medium spacing"
                                                v-html="delivery.PRICE_FORMATED"
                                            ></span>
                                        </span>
                                    </span>
                                </Radio>
                            </div>

                            <div class="form__field mt-36 mt-m-30">
                                <Input v-model="country" name="country" />
                                <div class="form__button">
                                    <button class="button button_secondary">Поменять страну</button>
                                </div>
                            </div>

                            <div class="form__field mt-36 mt-m-30">
                                <SelectAutocomplete v-model="property['CITY']"></SelectAutocomplete>
                            </div>

                            <div class="form__field mt-36 mt-m-30">
                                <Input placeholder="улица" v-model="property['STREET']" />
                            </div>

                            <div class="form__field mt-36 mt-m-30">
                                <Input placeholder="дом" v-model="property['HOUSE']" />
                            </div>

                            <!-- <div class="form__field mt-36 mt-m-30">
                                <Checkbox text="Сохранить адрес" name="save-adress" />
                            </div> -->

                            <!-- TODO:
                            <div class="order-form__item mt-36">
                                <a href="#" class="link">
                                    <span class="g-row g-row_middle">
                                        <span class="g-col d-block">
                                            <span class="link__icon">
                                                <SvgIconArrowNext />
                                            </span>
                                        </span>
                                        <span class="g-col d-block">
                                            <span class="uppercase fw-medium spacing">отправить в подарок</span>
                                        </span>
                                    </span>
                                </a>
                            </div>
                            -->

                            <h3 class="fs-16 uppercase fw-medium mt-70 mt-t-44" v-show="paymentsList">оплата</h3>
                            <div class="pt-20" v-show="paymentsList">
                                <Radio
                                    name="payment"
                                    value="cash"
                                    className="radio_border mt-12"
                                    v-for="payment in paymentsList"
                                    :key="payment.ID"
                                    v-model="currentPayment"
                                    :option="payment.ID"
                                >
                                    <!-- <img src="/images/payment.png" alt="payment" /> -->
                                    <span class="uppercase fw-medium spacing d-block" v-html="payment.NAME"></span>
                                </Radio>
                            </div>

                            <div class="form__field mt-36 mt-m-30">
                                <Checkbox text="Я согласен с условиями" name="agree">
                                    <NuxtLink to="/offer" class="link link_underline" target="_blank">
                                        обработки персональных данных
                                    </NuxtLink>
                                </Checkbox>
                            </div>

                            <div class="mt-70 mt-t-44">
                                <ClientOnly>
                                    <button
                                        class="button button_primary button_lg button_full uppercase fs-s-10 button_s-lg"
                                        type="button"
                                        @click.prevent="createOrder"
                                    >
                                        Оформить заказ
                                    </button>
                                </ClientOnly>
                            </div>
                            <div class="order-form__errors" v-show="errorsCreated.length">
                                <div
                                    class="order-form__error fs-16 uppercase fw-medium mt-t-44"
                                    v-for="error in errorsCreated"
                                >
                                    {{ error }}
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<style lang="scss" scoped>
.loading-wait {
    opacity: 0.5;
    cursor: default;
    pointer-events: none;
}
.order-form {
    max-width: 548px;
    &__border {
        padding: 24px;
        border: 1px solid #a8a8a8;
        border-radius: 7px;
    }
    &__item {
        border-bottom: 1px solid #a8a8a8;
        border-top: 1px solid #a8a8a8;
        padding: 36px 0;
    }
    @media screen and (max-width: 992px) {
        max-width: none;
    }
    @media screen and (max-width: 768px) {
        &__border {
            padding: 15px;
        }
    }

    &__errors {
        margin-top: 1em;
    }

    &__error {
        color: red;
    }
}
</style>
