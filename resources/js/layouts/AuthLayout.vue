<script setup lang="ts">
import NotificationStack from '@/components/notifications/NotificationStack.vue';
import { useFlashNotifications } from '@/composables/useNotifications';
import AuthLayout from '@/layouts/auth/AuthSimpleLayout.vue';

const { title = '', description = '' } = defineProps<{
    title?: string;
    description?: string;
}>();

useFlashNotifications();
</script>

<template>
    <AuthLayout :title="title" :description="description">
        <slot />
    </AuthLayout>
    <NotificationStack />
</template>
