<script setup lang="ts">
import NotificationStack from '@/components/notifications/NotificationStack.vue';
import { useFlashNotifications } from '@/composables/useNotifications';
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItem } from '@/types';

const { breadcrumbs = [] } = defineProps<{
    breadcrumbs?: BreadcrumbItem[];
}>();

useFlashNotifications();
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AppLayout>
    <NotificationStack />
</template>
