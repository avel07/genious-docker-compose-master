<script setup lang="ts">
import _debounce from 'lodash.debounce';

const basket = useBasket();
const basketData = basket.get();

const isError = ref(false);
const availableCoupon = ref();
const coupon = ref(basketData.value?.coupon ?? '');

watch(
    coupon,
    _debounce(async (newValue: string | number) => {
        await setCoupon(newValue);
    }, 600)
);

const setCoupon = async (couponValue: string | number) => {
    // Запрашиваем промокод
    const { data, error } = await useFetchApi(`/api/v1/coupon`, {
        method: 'POST',
        params: {
            coupon: couponValue
        },
        transform: (response: any) => response?.data,
        server: false,
        onRequestError: async (error) => {
            console.log(error);
        }
    });

    // Показываем ошибку, что такого промокода нет
    if (isRef(error) && error.value && error.value?.status !== 201) {
        // Если до этого был рабочий купон, то нужно будет обновить корзину
        if (availableCoupon.value) {
            availableCoupon.value = null;
            basket.load();
        }
        isError.value = true;
        setTimeout(() => (isError.value = false), 2500);
    } else if (data.value && data.value?.coupon) {
        availableCoupon.value = data.value?.coupon;
        basket.load();
    }
    return data;
};
</script>

<template>
    <div class="form promo-form">
        <div class="text-light-grey">Промокод:</div>
        <div class="form__field promo-form__field">
            <Input name="promo" className="promo-form__input" :class="{ 'is-error': isError }" v-model="coupon" />
            <div class="form__error promo-form__error text-red fs-12 text-center" v-if="isError">
                Такого промокода нет
            </div>
        </div>
    </div>
</template>

<style lang="scss">
@import './promo-form.scss';
</style>
