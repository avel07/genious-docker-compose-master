import type { Ref } from 'vue';

interface BasketProduct {
    [key: string]: any;
}

interface BasketData {
    coupon?: string;
    discount: number;
    discountFormat: string;
    items: BasketProduct[];
    priceItems: number;
    priceItemsFormat: string;
    total: number;
    totalFormat: string;
}

const useBasketState = () => useState('basket', () => <BasketData>{});

class Basket {
    _basketState: Ref<BasketData>;
    constructor() {
        this._basketState = useBasketState();
        this.load();
    }

    async load() {
        const { data } = await useFetchApi(`/api/v1/basket`, {
            method: 'GET',
            params: {},
            transform: (response: any) => response?.data,
            server: false
        });
        this._basketState.value = data.value;
        return this.get();
    }

    get() {
        return this._basketState;
    }

    async add(productId: string | number) {
        const { data } = await useFetchApi(`/api/v1/basket/add`, {
            method: 'POST',
            params: {
                id: productId
            },
            transform: (response: any) => response?.data,
            server: false
        });

        // Записываем результат
        this._basketState.value = data.value.basket;
        // Отдаем ответ
        return {
            action: this._basketState.value?.items[data.value?.action],
            basket: this._basketState.value
        };
    }

    async delete(productId: string | number) {
        const { data } = await useFetchApi(`/api/v1/basket/delete`, {
            method: 'POST',
            params: {
                id: productId
            },
            onRequestError: (request) => {
                console.log(request);
            },
            transform: (response: any) => response?.data,
            server: false
        });

        // Записываем результат
        this._basketState.value = data.value.basket;
        // Отдаем ответ
        return {
            action: this._basketState.value?.items[data.value?.action],
            basket: this._basketState.value
        };
    }

    async quantity(productId: string | number, quntity: number) {
        const { data } = await useFetchApi(`/api/v1/basket/quantity`, {
            method: 'POST',
            params: {
                id: productId,
                qty: quntity
            },
            onRequestError: (request) => {
                console.log(request);
            },
            transform: (response: any) => response?.data,
            server: false
        });

        // Записываем результат
        this._basketState.value = data.value.basket;
        // Отдаем ответ
        return {
            action: this._basketState.value?.items[data.value?.action],
            basket: this._basketState.value
        };
    }
}

export default defineNuxtPlugin(async () => ({
    provide: {
        basket: new Basket()
    }
}));
