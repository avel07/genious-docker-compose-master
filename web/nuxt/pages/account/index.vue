<script setup lang="ts">
import SvgIconBack from '~/assets/img/back.svg?component';

const { auth } = useAuth();
const isAuth = computed(() => !!auth.value);

if (process.client && !isAuth.value) {
    navigateTo('/');
}

const name = ref(auth.value?.NAME);
const lastName = ref(auth.value?.LAST_NAME);
const email = ref(auth.value?.EMAIL);
const phone = ref(auth.value?.PERSONAL_PHONE);
const password = ref('');

const setUser = async () => {
    const { data, error } = await useFetchApi(`/api/v1/user`, {
        method: 'POST',
        params: {
            NAME: name.value,
            LAST_NAME: lastName.value,
            EMAIL: email.value,
            PERSONAL_PHONE: phone.value,
            password: password.value
        },
        transform: (response: any) => response?.data,
        server: false
    });

    if (isRef(error) && error.value) {
        console.log(error);
        // TODO: Error
        return;
    }

    // Обновляем поля в глобальной шине
    auth.value = data.value;
};
</script>

<template>
    <main class="page">
        <div class="content content_small">
            <div class="page-form page-form_center">
                <div class="g-row g-row_middle g-row_center g-row_m-start">
                    <div class="g-col m-show">
                        <a href="#" class="link-icon">
                            <SvgIconBack />
                        </a>
                    </div>

                    <div class="g-col">
                        <h1 class="fs-32 uppercase fs-m-21 fs-s-13 fw-medium">настройки профиля</h1>
                    </div>
                </div>
                <ClientOnly v-if="isAuth">
                    <form class="form">
                        <div class="mt-48 mt-m-30 uppercase fs-16 fw-medium spacing">Личные данные</div>

                        <div class="form__field mt-40">
                            <Input placeholderText="Имя" v-model="name" />
                        </div>

                        <div class="form__field mt-40">
                            <Input placeholderText="фамилия" v-model="lastName" />
                        </div>

                        <!-- <div class="form__field mt-40">
                            <Datepicker />
                        </div> -->

                        <div class="form__field mt-40">
                            <Input placeholderText="Телефон" type="tel" v-model="phone" />
                        </div>

                        <div class="form__field mt-40">
                            <Input placeholderText="Электронная почта" type="email" v-model="email" />
                        </div>

                        <div class="form__field mt-40">
                            <FormPassword placeholderText="Изменить пароль" v-model="password" />
                        </div>

                        <!-- <div class="mt-56 uppercase fs-16 fw-medium spacing">Уведомления и подписка</div>

                        <div class="form__field mt-40">
                            <Input placeholderText="Электронная почта" name="email" type="email" value="ivanov@gmail.com" />
                        </div> -->

                        <!-- <div class="mt-24">
                            <Checkbox text="Рассылки о скидках и акциях" name="letters-discount" />
                        </div>

                        <div class="mt-12">
                            <Checkbox text="Уведомления о товарах в вишлисте" name="letters-wishlist" />
                        </div>

                        <div class="mt-12">
                            <Checkbox text="Уведомления об изменени статуса заказа" name="letters-status" />
                        </div> -->

                        <div class="mt-70 mt-t-44">
                            <button
                                class="button button_primary button_lg button_full uppercase button_s-lg"
                                type="submit"
                                @click.prevent="setUser"
                            >
                                сохранить изменения
                            </button>
                        </div>
                    </form>
                </ClientOnly>
            </div>
        </div>
    </main>
</template>
