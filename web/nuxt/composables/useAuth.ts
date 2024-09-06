// Импорт Ref из Vue
import { Ref } from 'vue';

// Интерфейс Auth
export interface Auth {
    EMAIL: string;
    ID: Number;
    LAST_NAME?: string;
    NAME: string;
    PERSONAL_BIRTHDAY?: string;
    PERSONAL_PHONE?: string;
}

// Ключ для сохранения данных авторизации
const keyStorageAuthData = 'authData';

// Основной хук useAuth для работы с авторизацией
const useAuth = (): {
    auth: Ref<Auth | null>;
    login: (email: string, password: string) => Promise<Boolean>;
    register: (
        name: string,
        lastName: string,
        date: string,
        phone: string,
        email: string,
        password: string,
        passwordRepeat: string
    ) => Promise<any>;
    logout: () => void;
} => {
    // Композиция для получения и установки состояния авторизации
    const useAuthState = (): Ref<Auth | null> => {
        return useState<Auth | null>(keyStorageAuthData, () => null);
    };

    // Композиция для получения и установки состояния авторизации
    const useAuthStateLoading = (): Ref<Boolean> => {
        return useState<Boolean>(keyStorageAuthData + 'Loading', () => false);
    };

    const auth = process.client ? useAuthState() : ref(null);
    const loadingAuth = process.client ? useAuthStateLoading() : ref(false);
    if (process.client && !auth.value && !loadingAuth.value) {
        loadingAuth.value = true;
        useFetchApi(`/api/v1/user`, {
            method: 'GET',
            transform: (response: any) => response?.data,
            server: false,
            lazy: false,
            key: 'api-user-fetch'
        })
            .then(({ data }) => {
                watch(data, (user) => {
                    auth.value = user;
                });
            })
            .finally(() => {
                loadingAuth.value = false;
            });
    }

    // Авторизация
    const login = async (email: string, password: string): Promise<Boolean> => {
        const { data: authData, error } = await useFetchApi(`/api/v1/user/login`, {
            method: 'POST',
            params: {
                email,
                password
            },
            transform: (response: any) => response?.data,
            server: false
        });

        if (isRef(error) && error.value) {
            return false;
        }

        if (process.client && authData) {
            auth.value = authData.value;
        }

        return process.client && !!auth.value;
    };

    // Регистрация
    const register = async (
        name: string,
        lastName: string,
        date: string,
        phone: string,
        email: string,
        password: string,
        passwordRepeat: string
    ): Promise<any> => {
        const { data: authData, error } = await useFetchApi(`/api/v1/user/register`, {
            method: 'POST',
            params: {
                name,
                lastName,
                date,
                phone,
                email,
                password,
                passwordRepeat
            },
            server: false
        });

        if (isRef(error) && error.value) {
            return error.value.data;
        }

        if (process.client && authData) {
            auth.value = authData.value?.data;
        }

        return authData.value;
    };

    const logout = () => {
        useFetchApi(`/api/v1/user/logout`, {
            method: 'POST',
            server: false
        });
        auth.value = null;
    };

    return {
        auth,
        login,
        register,
        logout
    };
};

export default useAuth;
