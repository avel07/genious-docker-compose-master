<script setup lang="ts">
import SvgIconClose from '~/assets/img/close.svg?component';

const props = defineProps({
    text: {
        type: String,
        default: ''
    }
});

const emit = defineEmits(['onClose']);
</script>

<template>
    <div class="g-col pt-8">
        <div class="sorting-button text-white">
            <div class="sorting-button__text uppercase spacing fw-medium">
                {{ props.text }}
            </div>
            <div class="sorting-button__icon" @click="emit('onClose')">
                <SvgIconClose />
            </div>
        </div>
    </div>
</template>

<style lang="scss">
@import './sorting-button.scss';
</style>
