<script setup>
import SvgIconClose from '~/assets/img/close.svg?component';

// Получение каталога
const { data: webform } = await useFetchApi(`/api/v1/webform/1`, {
    method: 'GET',
    key: 'api-webform-1', // Кеш для гидрации
    server: false,
    lazy: true,
    transform: (response) => response.data
});

const formModel = ref({});
const isOpen = ref(false);

const openPopup = () => {
    isOpen.value = true;
};
const closePopup = () => {
    isOpen.value = false;
};

const errorForm = ref();
const successForm = ref(false);
const send = async () => {
    // Получение каталога
    const { error } = await useFetchApi(`/api/v1/webform/1`, {
        body: {
            values: formModel.value
        },
        method: 'POST',
        server: false,
        transform: (response) => response.data
    });

    if (isRef(error) && error.value) {
        errorForm.value = error.value.data?.errors?.[0].message;
        setTimeout(() => {
            errorForm.value = undefined;
        }, 5000);
        return;
    }

    successForm.value = true;
    return;
};
</script>

<template>
    <button class="button button_dark-border button_lg button_full uppercase fs-s-13 button_s-lg" @click="openPopup()">
        {{ webform?.form.name }}
    </button>

    <div class="popup" v-if="webform && isOpen">
        <div class="popup__layout" @click="closePopup()"></div>
        <div class="popup__wrapper popup__wrapper_bottom">
            <div class="popup__block bg-white popup__block_sm">
                <div class="popup__close" @click="closePopup()">
                    <SvgIconClose />
                </div>
                <div class="fs-16 uppercase spacing fw-medium">{{ webform.form.name }}</div>

                <form class="form" @submit.prevent="send" v-if="!successForm">
                    <div class="form__field mt-40" v-for="input in webform.questions" :key="input.caption">
                        <Input :placeholderText="input.caption" v-model="formModel[input.field]" :type="input.type" />
                    </div>

                    <div class="mt-32 popup__error" v-if="errorForm" v-html="errorForm"></div>

                    <div class="mt-32">
                        <button
                            class="button button_primary button_lg button_full uppercase fs-s-10 button_s-lg"
                            type="submit"
                        >
                            {{ webform.form.button }}
                        </button>
                    </div>
                </form>

                <div class="fs-16 uppercase spacing fw-medium mt-32" v-else v-html="webform.form.desc"></div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            isOpen: false
        };
    },
    methods: {
        openPopup() {
            this.isOpen = true;
        },
        closePopup() {
            this.isOpen = false;
        }
    }
};
</script>

<style lang="scss">
@import './popup.scss';
</style>
