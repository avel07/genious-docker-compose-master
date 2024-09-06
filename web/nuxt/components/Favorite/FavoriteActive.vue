<script setup>
import SvgIconHeart from '~/assets/img/heart.svg';
</script>

<template>
    <div class="favorite" @click="toggleState()">
        <div :class="'button-icon ' + className">
            <div class="button-icon__svg" v-if="isActive">
                <SvgIconHeartFill />
            </div>
            <div class="button-icon__svg" v-else>
                <SvgIconHeart />
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        className: {
            type: String,
            default: ''
        }
    },
    data() {
        return {
            isActive: true
        };
    },
    methods: {
        toggleState() {
            this.isActive = !this.isActive;
        }
    }
};
</script>

<style lang="scss">
@import './favorite.scss';
</style>
