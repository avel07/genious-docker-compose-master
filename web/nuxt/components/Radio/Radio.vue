<template>
    <label :class="'radio ' + className">
        <input v-model="value" :value="option" :disabled="disabled" type="radio" class="radio__input" />
        <span class="radio__wrapper">
            <span class="radio__icon"></span>

            <span class="radio__content">
                <slot></slot>
            </span>
        </span>
    </label>
</template>

<script lang="ts">
export default {
    name: 'Radio',
    emits: ['update:modelValue'],
    computed: {
        value: {
            get() {
                return this.modelValue;
            },
            set(value: any) {
                this.$emit('update:modelValue', value);
            }
        }
    },
    props: {
        modelValue: {
            type: [String, Number],
            default: false
        },
        disabled: {
            type: Boolean,
            default: false
        },
        className: {
            type: String,
            default: ''
        },
        option: {
            type: String,
            default: ''
        }
    }
};
</script>

<style lang="scss">
@import './radio.scss';
</style>
