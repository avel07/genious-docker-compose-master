<template>
    <input
        :name="name"
        :type="type"
        :placeholder="placeholderText"
        :class="'input ' + className"
        :required="required"
        v-model="value"
    />
</template>

<script>
export default {
    name: 'Input',
    emits: ['update:modelValue'],
    computed: {
        value: {
            get() {
                return this.modelValue;
            },
            set(value) {
                this.$emit('update:modelValue', value);
            }
        }
    },
    props: {
        className: {
            type: String,
            default: ''
        },
        name: {
            type: String,
            default: ''
        },
        type: {
            type: String,
            default: 'text'
        },
        placeholderText: {
            type: String,
            default: ''
        },
        modelValue: {
            type: [String, Number],
            default: ''
        },
        required: {
            type: Boolean,
            default: false
        }
    }
};
</script>

<style lang="scss">
@import './input.scss';
</style>
