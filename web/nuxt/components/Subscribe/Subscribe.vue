<script setup lang="ts">
const email = ref();
const errorSubscribe = ref();
const successSubscribe = ref();
const subscribe = async () => {
    const { data, error } = await useFetchApi(`/api/v1/subscribe`, {
        method: 'POST',
        params: {
            email: email
        },
        server: false
    });

    if (isRef(error) && error.value) {
        errorSubscribe.value = error.value.data?.errors?.[0];
        setTimeout(() => {
            errorSubscribe.value = undefined;
        }, 5000);
    }

    if (data.value?.data === true) {
        successSubscribe.value = true;
        setTimeout(() => {
            successSubscribe.value = false;
        }, 10000);
    }
};
</script>

<template>
    <div class="subscribe">
        <div class="subscribe__title uppercase spacing fw-medium" v-if="!successSubscribe">
            Узнавайте последние <br />новости от нас
        </div>

        <form class="subscribe__form" @submit.prevent="subscribe" v-if="!successSubscribe">
            <div class="mt-24">
                <Input placeholderText="электронная почта" className="input_white" v-model="email" type="email" />
            </div>
            <div class="mt-40">
                <button class="button button_white-border button_lg button_full uppercase button_s-sm" type="submit">
                    подписаться
                </button>
            </div>
        </form>

        <div class="subscribe__title uppercase spacing fw-medium mt-40" v-else>Спасибо за подписку!</div>
        <div class="subscribe__error" v-if="errorSubscribe?.message">{{ errorSubscribe.message }}</div>
    </div>
</template>

<style lang="scss">
@import './subscribe.scss';
</style>
