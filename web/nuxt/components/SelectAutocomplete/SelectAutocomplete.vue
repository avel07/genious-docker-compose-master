<script setup lang="ts">
const props = defineProps({
    country: {
        default: ''
    },
    modelValue: {
        type: String,
        default: ''
    }
});

const selectCity = ref();
const filteredCities = ref();

const { data: citiesList, refresh } = await useFetchApi(
    `/api/v1/location/cities${props.country ? '?code=' + props.country : ''}`,
    {
        server: false,
        transform: (response: any) => response?.data,
        onResponse({ response }) {
            let cities = response._data?.data;
            if (cities && props.modelValue) {
                let findDefault = cities?.find((item: any) => item?.['code'].includes(props.modelValue));
                selectCity.value = findDefault?.['name'];
                emit('selected', findDefault);
            }
        }
    }
);

const searchCities = (event: any) => {
    filteredCities.value = citiesList.value.filter((item: any) =>
        item?.['name'].toLowerCase().includes(event.query.toLowerCase())
    );
};

const emit = defineEmits(['update:modelValue', 'selected']);
const select = (event: any) => {
    emit('update:modelValue', event.value?.code);
    emit('selected', event.value);
};
</script>

<template>
    <AutoComplete
        placeholder="Город"
        inputClass="input"
        v-model="selectCity"
        @item-select="select"
        :suggestions="filteredCities"
        @complete="searchCities"
        optionLabel="name"
    >
        <template #item="slotProps">
            <div class="p-city-item">
                <div class="ml-2">
                    <strong>{{ slotProps.item.name }}</strong
                    ><span>, {{ slotProps.item.parent }}</span>
                </div>
            </div>
        </template>
    </AutoComplete>
</template>

<style lang="scss">
.p-hidden-accessible {
    display: none;
}

.p-autocomplete {
    display: block;
}

.p-autocomplete-items {
    background: #fff;
    border: 1px solid;
}

.p-autocomplete-item {
    padding: 0.5em;
    &:hover {
        background: #c1c1c1;
    }
}
</style>
