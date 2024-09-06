<script setup>
import SvgIconHeart from '~/assets/img/heart.svg?component';
import SvgIconHeartFill from '~/assets/img/heart-fill.svg?component';
const { whishlist, addToWhishlist, removeFromWhishlist } = useWhishlist();

const props = defineProps({
    productId: {
        default: '',
        required: true
    },
    className: {
        type: String,
        default: ''
    }
});

const isActive = computed(() => {
    return whishlist.value?.ids.includes(props.productId);
});
</script>

<template>
    <ClientOnly>
        <div
            class="favorite"
            @click="isActive ? removeFromWhishlist(props.productId) : addToWhishlist(props.productId)"
        >
            <div :class="'button-icon ' + className">
                <div class="button-icon__svg" v-if="isActive">
                    <SvgIconHeartFill />
                </div>
                <div class="button-icon__svg" v-else>
                    <SvgIconHeart />
                </div>
            </div>
        </div>
    </ClientOnly>
</template>

<style lang="scss">
@import './favorite.scss';
</style>
