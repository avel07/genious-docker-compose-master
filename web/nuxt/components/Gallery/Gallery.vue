<script setup>
import SvgIconFull from '~/assets/img/full.svg';
import '@fancyapps/ui/dist/fancybox.css';

const Fancybox = ref();
onMounted(async () => {
    // async load
    if (process.client) {
        const { Fancybox: FancyboxModule } = await import('@fancyapps/ui/src/Fancybox/Fancybox');
        Fancybox.value = FancyboxModule;
    }
});

const props = defineProps({
    images: {
        default: []
    }
});

const gallery = computed(() => {
    if (props.images) {
        return props.images.map((image) => ({
            src: image
        }));
    } else {
        return [];
    }
});

const startFancy = () => {
    if (process.client) {
        return Fancybox.value.show(gallery.value, {
            Toolbar: {
                display: ['close']
            },
            Thumbs: {
                autoStart: false
            }
        });
    }
};
</script>

<template>
    <div class="button-icon button-icon_lg bg-white" v-if="gallery" @click="startFancy()">
        <span class="button-icon__svg">
            <SvgIconFull />
        </span>
    </div>
</template>
