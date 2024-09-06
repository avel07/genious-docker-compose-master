<script setup lang="ts">
import _debounce from 'lodash.debounce';
import SvgIconSearch from '~/assets/img/search.svg?component';
import SvgIconClose from '~/assets/img/close.svg?component';

const route = useRoute();

const searchText = ref();
const isFill = ref(false);

const changeFill = () => {
    if (searchText.value === '') {
        isFill.value = false;
    } else {
        isFill.value = true;
    }
};

const resetField = () => {
    isFill.value = false;
    searchText.value = '';
};

// Поиск (переход на страницу и query обновление)
const submit = () => {
    navigateTo('/search?query=' + encodeURI(searchText.value));
    if (route.path != '/search') {
        emit('submit');
    }
};

// Автообновление при поиске на самой странице
if (route.path == '/search' && process.client) {
    searchText.value = route.query?.query;
    watch(
        searchText,
        _debounce(() => {
            submit();
        }, 300)
    );
}

const emit = defineEmits(['submit']);
</script>

<template>
    <form class="search" @submit.prevent="submit">
        <div class="search__field">
            <input
                v-model="searchText"
                type="text"
                placeholder="что вас интересует?"
                class="input search__input"
                @input="changeFill()"
            />
        </div>
        <div class="search__icon">
            <SvgIconSearch />
        </div>
        <div class="search__button" v-if="isFill" @click="resetField()">
            <SvgIconClose />
        </div>
    </form>
</template>

<style lang="scss">
@import './search.scss';
</style>
