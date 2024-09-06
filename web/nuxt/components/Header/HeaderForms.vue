<script setup lang="ts">
const activeEl = ref(1);

const { login, register } = useAuth();

const email = ref('');
const password = ref('');
const passwordRepeat = ref('');
const name = ref('');
const phone = ref('');
const date = ref('');
const lastName = ref('');

const error = ref(false);
const errorMessage = ref();

// Авторизация
const loginForm = async () => {
    const result = await login(email.value, password.value);
    if (!result) {
        password.value = '';
        error.value = true;
        setTimeout(() => {
            error.value = false;
        }, 3000);
    }
};

// Регистрация
const registerForm = async () => {
    const result = await register(
        name.value,
        lastName.value,
        date.value,
        phone.value,
        email.value,
        password.value,
        passwordRepeat.value
    );
    if (result?.status && result.status == 'error') {
        error.value = true;
        errorMessage.value = result?.errors;
        setTimeout(() => {
            error.value = false;
            errorMessage.value = null;
        }, 5000);
    }
};
</script>

<template>
    <div v-if="activeEl === 1">
        <div class="fs-21 uppercase fw-medium spacing">ВХОД</div>
        <form class="form" @submit.prevent="loginForm">
            <div class="form__field mt-40">
                <Input
                    placeholderText="Электронная почта"
                    required
                    v-model="email"
                    type="email"
                    :className="error ? 'is-error' : ''"
                />
            </div>

            <div class="form__field mt-40">
                <FormPassword
                    placeholderText="password"
                    required
                    v-model="password"
                    :className="error ? 'is-error' : ''"
                />
            </div>

            <div class="mt-20">
                <div class="link link_underline" @click="activeEl = 3">Забыли пароль?</div>
            </div>

            <div class="mt-48">
                <button class="button button_full button_primary button_lg button_s-lg uppercase" type="submit">
                    войти
                </button>
            </div>

            <div class="mt-32">
                У вас еще нет учетной записи?
                <div class="link link_underline" @click="activeEl = 2">Зарегистрируйтесь</div>
            </div>
        </form>
    </div>

    <div v-if="activeEl === 2">
        <div class="fs-21 uppercase fw-medium spacing">регистрация</div>

        <form class="form" @submit.prevent="registerForm">
            <div class="form__field mt-40">
                <Input placeholderText="Имя" v-model="name" />
            </div>

            <div class="form__field mt-40">
                <Input placeholderText="фамилия" v-model="lastName" />
            </div>

            <div class="form__field mt-40">
                <Input placeholderText="Телефон" v-model="phone" type="tel" />
            </div>

            <div class="form__field mt-40">
                <Input placeholderText="Электронная почта" v-model="email" type="email" />
            </div>

            <div class="form__field mt-40">
                <FormPassword placeholderText="password" v-model="password" />
            </div>

            <div class="form__field mt-40">
                <FormPassword placeholderText="password" v-model="passwordRepeat" />
            </div>

            <div
                class="mt-48 error"
                v-for="error in errorMessage"
                v-if="errorMessage && errorMessage?.length > 0"
                v-html="error.message"
            ></div>

            <div class="mt-48">
                <button class="button button_primary button_lg button_full uppercase fs-s-10 button_s-lg" type="submit">
                    Зарегистрироваться
                </button>
            </div>
        </form>

        <div class="mt-32">
            Уже есть учетная запись?
            <div class="link link_underline" @click="activeEl = 1">Войдите</div>
        </div>
    </div>

    <div v-if="activeEl === 3">
        <div class="fs-21 uppercase fw-medium spacing">Восстановление пароля</div>

        <form class="form">
            <div class="mt-12">
                Если вы забыли пароль, введите электронную почту и мы по отправим указания по его восстановлению.
            </div>

            <div class="form__field mt-40">
                <Input placeholderText="Электронная почта" name="email" type="email" />
            </div>

            <div class="mt-48">
                <button class="button button_full button_primary button_lg button_s-lg uppercase" @click="activeEl = 4">
                    Отправить
                </button>
            </div>
        </form>
    </div>

    <div v-if="activeEl === 4">
        <div class="fs-21 uppercase fw-medium spacing">Восстановление пароля</div>

        <div class="mt-12">Отлично! Мы направили инструкции по восстановлению пароля на вашу почту.</div>

        <div class="mt-48">
            <button class="button button_full button_primary button_lg button_s-lg uppercase" @click="activeEl = 1">
                войти
            </button>
        </div>
    </div>
</template>

<style scoped>
.error {
    color: red;
}
</style>
