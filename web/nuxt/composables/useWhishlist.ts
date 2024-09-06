import { ref, Ref } from 'vue';

export interface Whishlist {
    ids: string[];
}

const useWhishlist = (): {
    whishlist: Ref<Whishlist>;
    addToWhishlist: (productId: string) => void;
    removeFromWhishlist: (productId: string) => void;
} => {
    const defaultWhishlist = { ids: [] };
    const key = 'whishlist';

    // Создаем реактивную переменную для хранения списка избранного
    const whishlist = ref<Whishlist>(defaultWhishlist);

    // Загружаем избранное из localStorage
    if (process.client) {
        const data = localStorage.getItem(key);
        if (data) {
            whishlist.value = JSON.parse(data);
        }
    }

    // Добавляем товар в избранное
    const addToWhishlist = (productId: string): void => {
        const idsSet = new Set(whishlist.value.ids);

        // Проверяем, есть ли товар уже в списке избранного
        if (!idsSet.has(productId)) {
            idsSet.add(productId);
            whishlist.value.ids = Array.from(idsSet);

            // Сохраняем список избранного в localStorage
            if (process.client) {
                localStorage.setItem(key, JSON.stringify(whishlist.value));
            }
        }
    };

    // Удаляем товар из избранного
    const removeFromWhishlist = (productId: string): void => {
        const idsSet = new Set(whishlist.value.ids);
        const index = whishlist.value.ids.indexOf(productId);

        // Проверяем, есть ли товар в списке избранного
        if (index !== -1) {
            idsSet.delete(productId);
            whishlist.value.ids = Array.from(idsSet);

            // Сохраняем список избранного в localStorage
            if (process.client) {
                localStorage.setItem(key, JSON.stringify(whishlist.value));
            }
        }
    };

    return {
        whishlist,
        addToWhishlist,
        removeFromWhishlist
    };
};

export default useWhishlist;
