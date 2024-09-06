<script setup lang="ts">
const { logout } = useAuth();
const emit = defineEmits(['close']);
</script>

<template>
    <div class="mt-8">
        <NuxtLink to="/orders" class="link uppercase spacing fw-medium" @click="emit('close')">заказы</NuxtLink>
    </div>
    <div class="mt-8">
        <NuxtLink to="/wishlist" class="link uppercase spacing fw-medium" @click="emit('close')">вишлист</NuxtLink>
    </div>
    <div class="mt-8">
        <NuxtLink to="/account" class="link uppercase spacing fw-medium" @click="emit('close')">настройки</NuxtLink>
    </div>

    <div class="mt-24">
        <a href="javascript:void(0)" @click.prevent="logout" class="link uppercase spacing fw-medium"
            >выйти из аккаунта</a
        >
    </div>
</template>
