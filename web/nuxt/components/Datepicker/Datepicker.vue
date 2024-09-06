<template>
    <ClientOnly>
        <DatePicker
            :lang="langObject"
            v-model:value="time"
            input-class="input"
            placeholder="дата рождения"
        ></DatePicker>
        <template #fallback>
            <Input placeholderText="дата рождения" />
        </template>
    </ClientOnly>
</template>

<script setup>
import 'vue-datepicker-next/index.css';

const locale = {
    months: [
        'январь',
        'февраль',
        'март',
        'апрель',
        'май',
        'июнь',
        'июль',
        'август',
        'сентябрь',
        'октябрь',
        'ноябрь',
        'декабрь'
    ],
    monthsShort: ['янв.', 'февр.', 'март', 'апр.', 'май', 'июнь', 'июль', 'авг.', 'сент.', 'окт.', 'нояб.', 'дек.'],
    weekdays: ['воскресенье', 'понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота'],
    weekdaysShort: ['вс', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб'],
    weekdaysMin: ['вс', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб'],
    firstDayOfWeek: 1,
    firstWeekContainsDate: 1
};

const lang = {
    formatLocale: locale,
    yearFormat: 'YYYY',
    monthFormat: 'MMM',
    monthBeforeYear: true,
    format: 'DD.MM.YYYY'
};

// Async component
const DatePicker = defineAsyncComponent(() => {
    let DatePickerModule = import('vue-datepicker-next').then((Module) => {
        Module.default.locale('ru', lang);
        return Module.default;
    });
    return DatePickerModule;
});

const langObject = ref({
    formatLocale: {
        firstDayOfWeek: 1
    }
});

const langString = ref('ru');
const time = ref();
</script>

<style lang="scss">
@import './datepicker.scss';
</style>
