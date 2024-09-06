<script setup>
import SvgIconEye from '~/assets/img/eye.svg';
import SvgIconEyeLine from '~/assets/img/eye-line.svg';
</script>

<template>
    <div class="form-password">
        <input
            :name="name"
            :type="passwordFieldType"
            :placeholder="placeholderText"
            :class="'input form-password__input ' + className"
            :required="required"
            v-model="value"
        />
        <div class="form-password__icon" @click="showPassword()" v-bind:class="{ 'is-close': isOpen }">
            <SvgIconEye />
            <div class="form-password__line">
                <SvgIconEyeLine />
            </div>
        </div>
    </div>
</template>

<script>
export default {
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
        placeholderText: {
            type: String,
            default: ''
        },
        required: {
            type: Boolean,
            default: false
        },
        modelValue: {
            type: [String, Number],
            default: ''
        }
    },
    data() {
        return {
            passwordFieldType: 'password',
            isOpen: false
        };
    },
    methods: {
        showPassword() {
            this.passwordFieldType = this.passwordFieldType === 'password' ? 'text' : 'password';
            this.isOpen = !this.isOpen;
        }
    }
};
</script>

<style lang="scss">
@import './form-password.scss';
</style>
