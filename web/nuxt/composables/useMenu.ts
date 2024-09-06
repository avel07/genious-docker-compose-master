import { Ref } from 'vue';
import { useState } from '#app';
import useFetchApi from '@@/composables/useFetchApi';

interface Menu {
    [key: string]: any;
}

// Ключ для сохранения данных меню
const keyStorageMenuAll = 'menuAll';

// Композиция для получения и установки состояния меню
const useMenuState = (): Ref<Menu | null> => {
    return useState<Menu | null>(keyStorageMenuAll, () => null);
};

// Композиция для получения меню из API
const useMenu = (): Ref<Menu | null> => {
    const menuItems = useMenuState();
    if (!menuItems.value) {
        useFetchApi('/api/v1/menu', {
            method: 'GET',
            transform: (response: any) => response?.data,
            server: true
        }).then(({ data }) => {
            menuItems.value = data.value;
        });
    }
    return menuItems;
};

export default useMenu;
