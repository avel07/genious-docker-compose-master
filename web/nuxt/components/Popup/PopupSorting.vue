<script setup>
import SvgIconArrowSelect from '~/assets/img/select-arrow.svg';
</script>

<template>
    <div class="button-group" @click="openPopup()">
        <div class="uppercase fw-medium spacing link">сортировка</div>
        <div class="button-group__icon text-light-grey">
            <SvgIconArrowSelect />
        </div>
    </div>

    <div class="popup" v-if="isOpen">
        <div class="popup__layout" @click="closePopup()"></div>
        <div class="popup__wrapper">
            <div class="popup__block">
                <div class="fs-16 uppercase spacing fw-medium">сортировать</div>

                <div class="popup-list mt-32">
                    <div class="popup-list__item">
                        <Radio name="sorting" value="по новизне" className="radio_square">
                            <span class="uppercase spacing fw-medium nowrap">по новизне</span>
                        </Radio>
                    </div>

                    <div class="popup-list__item">
                        <Radio name="sorting" value="по популярности" className="radio_square">
                            <span class="uppercase spacing fw-medium nowrap">по популярности</span>
                        </Radio>
                    </div>

                    <div class="popup-list__item">
                        <Radio name="sorting" value="по цене (по убыванию)" className="radio_square">
                            <span class="uppercase spacing fw-medium nowrap">по цене (по убыванию)</span>
                        </Radio>
                    </div>

                    <div class="popup-list__item">
                        <Radio name="sorting" value="по популярности" className="radio_square">
                            <span class="uppercase spacing fw-medium nowrap">по цене (по возрастанию)</span>
                        </Radio>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            isOpen: false
        };
    },
    methods: {
        openPopup() {
            this.isOpen = true;
        },
        closePopup() {
            this.isOpen = false;
        }
    }
};
</script>

<style lang="scss">
@import './popup.scss';
</style>
