<script setup lang="ts">
import { AlertOctagon, AlertTriangle, CheckCircle, Info, X } from 'lucide-vue-next';
import { computed } from 'vue';
import { cn } from '@/lib/utils';
import type { Notification, NotificationType } from '@/types/notifications';

const props = defineProps<{ notification: Notification }>();
const emit = defineEmits<{ dismiss: [id: string] }>();

const iconMap: Record<NotificationType, typeof Info> = {
    info: Info,
    success: CheckCircle,
    warning: AlertTriangle,
    danger: AlertOctagon,
};

const styleMap: Record<NotificationType, { border: string; icon: string }> = {
    info: { border: 'border-border', icon: 'text-foreground/60' },
    success: { border: 'border-border', icon: 'text-emerald-500 dark:text-emerald-400' },
    warning: { border: 'border-border', icon: 'text-amber-500 dark:text-amber-400' },
    danger: { border: 'border-destructive/50', icon: 'text-destructive' },
};

const icon = computed(() => iconMap[props.notification.type]);
const styles = computed(() => styleMap[props.notification.type]);
</script>

<template>
    <div
        :class="
            cn(
                'notification-item',
                'relative flex w-80 items-start gap-3 rounded-2xl border px-4 py-3.5',
                'bg-background/90 shadow-lg shadow-black/5 backdrop-blur-xl',
                'cursor-pointer select-none',
                styles.border,
            )
        "
        @click="emit('dismiss', notification.id)"
    >
        <component :is="icon" :class="cn('mt-px size-4 shrink-0', styles.icon)" />
        <div class="flex-1 space-y-0.5 pr-5">
            <p class="text-sm font-medium leading-snug text-foreground">
                {{ notification.title }}
            </p>
            <p v-if="notification.description" class="text-xs leading-relaxed text-muted-foreground">
                {{ notification.description }}
            </p>
        </div>
        <button
            class="absolute right-2.5 top-2.5 rounded-md p-0.5 text-muted-foreground/50 transition-colors hover:text-foreground"
            @click.stop="emit('dismiss', notification.id)"
        >
            <X class="size-3.5" />
        </button>
    </div>
</template>
