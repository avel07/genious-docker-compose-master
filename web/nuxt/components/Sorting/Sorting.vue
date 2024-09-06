<script setup lang="ts">
const route = useRoute();
const { params } = route;

const activeFilters = ref(<any>{}); // Активные фильтры (v-model)
const activeFiltersParams = ref(<any>{
    sectionCode: params?.section_code ? params.section_code : null
}); // Активные фильтры (params from filter)

// Проставляем значения для фильтра и делаем рефреш
watch(activeFilters.value, (newValues) => {
    for (const key in newValues) {
        if (Object.prototype.hasOwnProperty.call(newValues, key)) {
            const value = newValues[key];
            if (value) {
                activeFiltersParams.value[key] = 'Y';
            } else {
                delete activeFiltersParams.value[key];
            }
        }
    }
    refresh();
});

// Запрос к методу фильтров
const { data: filter, refresh: refresh } = await useFetchApi(() => '/api/v1/filter', {
    params: activeFiltersParams.value,
    transform: (response: any) => ({
        ELEMENTS: response?.data?.ELEMENTS,
        ITEMS: response?.data?.ITEMS
    })
});

// Отдаем в родительский компонент событие если изменились фильтры
const emit = defineEmits(['onFilterItems']);
watch(filter, (newFilterData) => {
    if (Object.keys(activeFilters.value)?.length) {
        emit('onFilterItems', newFilterData.ELEMENTS);
    }
});

// Получаем поля
const fields = computed(
    () =>
        filter.value?.ITEMS &&
        Object.values(filter.value?.ITEMS)?.map((item: any) => {
            return {
                CODE: item.CODE,
                NAME: item.NAME,
                ITEMS:
                    item.VALUES &&
                    Object.values(item.VALUES)?.map((value: any) => ({
                        ID: value.CONTROL_ID,
                        COUNT: value.ELEMENT_COUNT,
                        VALUE: value.VALUE?.replace(/^\.+/m, ''), // Удаляем дополнительно точки перед разделами
                        SORT: value.SORT,
                        CHECKED: value?.CHECKED ?? false,
                        DATA: value?.DATA ?? []
                    }))
            };
        })
);

// Активные фильтры в данный момент
const activeValues = computed(() => {
    let result = <any>[];
    if (fields.value && fields.value.length) {
        let activeFiltersId = Object.keys(activeFiltersParams.value);
        for (const key in fields.value) {
            if (Object.prototype.hasOwnProperty.call(fields.value, key)) {
                const field = fields.value[key];
                if (field?.ITEMS)
                    for (const key in field.ITEMS) {
                        if (Object.prototype.hasOwnProperty.call(field.ITEMS, key)) {
                            const value = field.ITEMS[key];
                            if (activeFiltersId.includes(value.ID)) {
                                result.push(value);
                            }
                        }
                    }
            }
        }
    }
    return result;
});

// Очистка всех фильтров
const clear = () => {
    for (const prop of Object.getOwnPropertyNames(activeFilters.value)) {
        activeFilters.value[prop] = false;
    }
};
</script>

<template>
    <div class="sorting mt-40 mt-m-30">
        <div class="m-hidden">
            <div class="g-row g-row_between g-row_middle">
                <div class="g-col" v-if="fields && fields.length">
                    <div class="g-row g-row_m-wrap">
                        <div
                            class="g-col g-col_m-100"
                            v-for="field in fields"
                            :key="field.CODE"
                            v-show="field.ITEMS?.length"
                        >
                            <Dropdown :text="field.NAME" className="dropdown_s-right">
                                <div class="pt-24" v-for="item in field.ITEMS" :key="item.ID">
                                    <div class="g-row g-row_middle g-row_between g-row_small">
                                        <div class="g-col">
                                            <Checkbox
                                                :disabled="item.COUNT == 0"
                                                :text="item.VALUE"
                                                :activeColor="item?.DATA?.HEX"
                                                v-model="activeFilters[item.ID]"
                                                textClass="uppercase spacing fw-medium"
                                            />
                                        </div>
                                        <div class="g-col">
                                            <div class="text-faded-grey fw-medium spacing">{{ item.COUNT }}</div>
                                        </div>
                                    </div>
                                </div>
                            </Dropdown>
                        </div>
                    </div>
                </div>

                <div class="g-col">
                    <Dropdown text="сортировать" className="dropdown_right">
                        <div class="pt-24">
                            <Radio name="sorting" value="по новизне" className="radio_square">
                                <span class="uppercase spacing fw-medium nowrap">по новизне</span>
                            </Radio>
                        </div>

                        <div class="pt-24">
                            <Radio name="sorting" value="по популярности" className="radio_square">
                                <span class="uppercase spacing fw-medium nowrap">по популярности</span>
                            </Radio>
                        </div>

                        <div class="pt-24">
                            <Radio name="sorting" value="по цене (по убыванию)" className="radio_square">
                                <span class="uppercase spacing fw-medium nowrap">по цене (по убыванию)</span>
                            </Radio>
                        </div>

                        <div class="pt-24">
                            <Radio name="sorting" value="по популярности" className="radio_square">
                                <span class="uppercase spacing fw-medium nowrap">по цене (по возрастанию)</span>
                            </Radio>
                        </div>
                    </Dropdown>
                </div>
            </div>

            <div
                class="g-row g-row_wrap g-row_small g-row_middle mt-16 mt-m-0"
                v-if="activeValues && activeValues.length"
            >
                <SortingButton
                    v-for="item in activeValues"
                    :key="item.ID"
                    :text="item.VALUE"
                    @onClose="activeFilters[item.ID] = false"
                />
                <div class="g-col pt-8">
                    <button class="button button_secondary uppercase spacing" type="button" @click="clear">
                        сбросить
                    </button>
                </div>
            </div>
        </div>

        <div class="m-show">
            <div class="g-row g-row_middle g-row_between g-row_s-small">
                <div class="g-col">
                    <PopupFilter />
                </div>

                <div class="g-col">
                    <PopupSorting />
                </div>
            </div>
        </div>
    </div>
</template>
