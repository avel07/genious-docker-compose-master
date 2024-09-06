<script setup>
import SvgIconPlay from '~/assets/img/play.svg';
</script>

<template>
    <div :class="'video ' + className">
        <video width="1920" height="1080" id="video" loop playsinline>
            <source :src="video" type="video/mp4" />
        </video>
        <div class="video__button" v-bind:class="{ 'is-active': isPlay }" @click="playVideo()">
            <div class="video__icon text-white">
                <SvgIconPlay />
            </div>
        </div>

        <div class="video__title uppercase fs-32 text-white fs-m-21 fs-s-13 fw-medium spacing">
            {{ name }}
        </div>
    </div>
</template>

<script>
export default {
    props: {
        name: {
            type: String,
            default: ''
        },
        video: {
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
            isPlay: false
        };
    },
    methods: {
        playVideo() {
            this.isPlay = !this.isPlay;
            const video = document.getElementById('video');
            if (video.paused === true) {
                video.play();
            } else {
                video.pause();
            }
        }
    }
};
</script>

<style lang="scss">
@import './video.scss';
</style>
