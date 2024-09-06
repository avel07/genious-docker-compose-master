<template>
    <label class="checkbox">
        <input v-model="value" :disabled="disabled" type="checkbox" class="checkbox__input" />
        <span class="checkbox__wrapper">
            <span class="checkbox__icon">
                <span class="checkbox__inside" :style="{ background: activeColor }"></span>
            </span>

            <span :class="'checkbox__text ' + textClass">
                {{ text }}
                <slot></slot>
            </span>
        </span>
    </label>
</template>

<script lang="ts">
export default {
    name: 'Checkbox',
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
            type: Boolean,
            default: false
        },
        disabled: {
            type: Boolean,
            default: false
        },
        text: {
            type: String,
            default: ''
        },
        textClass: {
            type: String,
            default: ''
        },
        activeColor: {
            type: String,
            default: '#000000'
        }
    }
};
</script>

<style lang="scss">
@import './checkbox.scss';
</style>
