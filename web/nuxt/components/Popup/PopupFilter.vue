<script setup>
import SvgIconArrowSelect from '~/assets/img/select-arrow.svg';
import SvgIconBack from '~/assets/img/back.svg';
</script>

<template>
    <div class="button-group" @click="openPopup()">
        <div class="uppercase fw-medium spacing link">фильтры(1)</div>
        <div class="button-group__icon text-light-grey">
            <SvgIconArrowSelect />
        </div>
    </div>

    <div class="popup" v-if="isOpen">
        <div class="popup__layout" @click="closePopup()"></div>
        <div class="popup__wrapper">
            <div class="popup__block" v-if="activeEl === 1">
                <div class="fs-16 uppercase spacing fw-medium">фильтры</div>

                <div class="popup-list mt-32">
                    <div class="popup-list__item" @click="activeEl = 2">
                        <div class="g-row g-row_middle g-row_between">
                            <div class="g-col">
                                <div class="uppercase fw-medium spacing">цвет</div>
                            </div>
                            <div class="g-col">
                                <div class="popup-list__icon text-light-grey">
                                    <SvgIconArrowSelect />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="popup-list__item" @click="activeEl = 3">
                        <div class="g-row g-row_middle g-row_between">
                            <div class="g-col">
                                <div class="uppercase fw-medium spacing">размер</div>
                            </div>
                            <div class="g-col">
                                <div class="popup-list__icon text-light-grey">
                                    <SvgIconArrowSelect />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="popup-list__item" @click="activeEl = 4">
                        <div class="g-row g-row_middle g-row_between">
                            <div class="g-col">
                                <div class="uppercase fw-medium spacing">категория</div>
                            </div>
                            <div class="g-col">
                                <div class="popup-list__icon text-light-grey">
                                    <SvgIconArrowSelect />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="popup__block" v-if="activeEl === 2">
                <div class="popup-nav" @click="activeEl = 1">
                    <div class="popup-nav__icon">
                        <SvgIconBack />
                    </div>
                    <div class="fs-16 uppercase spacing fw-medium">фильтры / цвет</div>
                </div>

                <div class="popup-list mt-32">
                    <div class="popup-list__item">
                        <div class="g-row g-row_middle g-row_between">
                            <div class="g-col">
                                <Checkbox
                                    text="красный"
                                    name="красный"
                                    activeColor="#FF3333"
                                    textClass="uppercase spacing fw-medium"
                                />
                            </div>
                            <div class="g-col">
                                <div class="text-faded-grey fw-medium spacing">2</div>
                            </div>
                        </div>
                    </div>

                    <div class="popup-list__item">
                        <div class="g-row g-row_middle g-row_between">
                            <div class="g-col">
                                <Checkbox
                                    text="зеленый"
                                    name="зеленый"
                                    activeColor="#1ABC00"
                                    textClass="uppercase spacing fw-medium"
                                />
                            </div>
                            <div class="g-col">
                                <div class="text-faded-grey fw-medium spacing">5</div>
                            </div>
                        </div>
                    </div>

                    <div class="popup-list__item">
                        <div class="g-row g-row_middle g-row_between g-row_small">
                            <div class="g-col">
                                <Checkbox
                                    text="белый"
                                    name="белый"
                                    activeColor="#ffffff"
                                    textClass="uppercase spacing fw-medium"
                                />
                            </div>
                            <div class="g-col">
                                <div class="text-faded-grey fw-medium spacing">10</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="popup__block" v-if="activeEl === 3">
                <div class="popup-nav" @click="activeEl = 1">
                    <div class="popup-nav__icon">
                        <SvgIconBack />
                    </div>
                    <div class="fs-16 uppercase spacing fw-medium">фильтры / размер</div>
                </div>

                <div class="popup-list mt-32">
                    <div class="popup-list__item">
                        <div class="g-row g-row_middle g-row_between g-row_small">
                            <div class="g-col">
                                <Checkbox text="S" name="S" textClass="uppercase spacing fw-medium" />
                            </div>
                            <div class="g-col">
                                <div class="text-faded-grey fw-medium spacing">2</div>
                            </div>
                        </div>
                    </div>

                    <div class="popup-list__item">
                        <div class="g-row g-row_middle g-row_between g-row_small">
                            <div class="g-col">
                                <Checkbox text="M" name="M" textClass="uppercase spacing fw-medium" />
                            </div>
                            <div class="g-col">
                                <div class="text-faded-grey fw-medium spacing">5</div>
                            </div>
                        </div>
                    </div>

                    <div class="popup-list__item">
                        <div class="g-row g-row_middle g-row_between g-row_small">
                            <div class="g-col">
                                <Checkbox text="L" name="L" textClass="uppercase spacing fw-medium" />
                            </div>
                            <div class="g-col">
                                <div class="text-faded-grey fw-medium spacing">10</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="popup__block" v-if="activeEl === 4">
                <div class="popup-nav" @click="activeEl = 1">
                    <div class="popup-nav__icon">
                        <SvgIconBack />
                    </div>
                    <div class="fs-16 uppercase spacing fw-medium">фильтры / категория</div>
                </div>

                <div class="popup-list mt-32">
                    <div class="popup-list__item">
                        <div class="g-row g-row_middle g-row_between g-row_small">
                            <div class="g-col">
                                <Checkbox text="платья" name="платья" textClass="uppercase spacing fw-medium" />
                            </div>
                            <div class="g-col">
                                <div class="text-faded-grey fw-medium spacing">2</div>
                            </div>
                        </div>
                    </div>

                    <div class="popup-list__item">
                        <div class="g-row g-row_middle g-row_between g-row_small">
                            <div class="g-col">
                                <Checkbox text="футболки" name="футболки" textClass="uppercase spacing fw-medium" />
                            </div>
                            <div class="g-col">
                                <div class="text-faded-grey fw-medium spacing">5</div>
                            </div>
                        </div>
                    </div>

                    <div class="popup-list__item">
                        <div class="g-row g-row_middle g-row_between g-row_small">
                            <div class="g-col">
                                <Checkbox text="Брюки" name="Брюки" textClass="uppercase spacing fw-medium" />
                            </div>
                            <div class="g-col">
                                <div class="text-faded-grey fw-medium spacing">10</div>
                            </div>
                        </div>
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
            isOpen: false,
            activeEl: 1
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
