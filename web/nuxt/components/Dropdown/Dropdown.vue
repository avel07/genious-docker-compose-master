<script setup>
import SvgIconArrowSelect from '~/assets/img/select-arrow.svg';
</script>

<template>
    <div :class="'dropdown ' + className" v-click-outside="clickOutside">
        <div class="dropdown__button" @click="toggleDropdown()" v-bind:class="{ 'is-active': isOpen }">
            <div class="dropdown__text uppercase fs-s-13 fw-medium spacing">
                {{ text }}
            </div>
            <div class="dropdown__arrow">
                <SvgIconArrowSelect />
            </div>
        </div>

        <div class="dropdown__block" v-if="isOpen">
            <slot></slot>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        text: {
            type: String,
            default: ''
        },
        className: {
            type: String,
            default: ''
        }
    },
    data() {
        return {
            isOpen: false
        };
    },
    methods: {
        toggleDropdown() {
            this.isOpen = !this.isOpen;
        },
        clickOutside() {
            this.isOpen = false;
        }
    }
};
</script>

<style lang="scss">
@import './dropdown.scss';
</style>
