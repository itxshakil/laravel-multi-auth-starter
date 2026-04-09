<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useNotifications } from '@/composables/useNotifications';
import NotificationItem from './NotificationItem.vue';

const { notifications, dismiss } = useNotifications();

// Defer Teleport until client mount to avoid SSR hydration mismatch.
const mounted = ref(false);
onMounted(() => {
 mounted.value = true;
});

const SPRING_IN = 'cubic-bezier(0.16, 1, 0.3, 1)';
const EASE_OUT = 'cubic-bezier(0.4, 0, 0.2, 1)';

function onBeforeEnter(el: Element): void {
    const h = el as HTMLElement;
    h.style.opacity = '0';
    h.style.transform = 'translateX(calc(100% + 1rem))';
}

function onEnter(el: Element, done: () => void): void {
    (el as HTMLElement)
        .animate(
            [
                { opacity: '0', transform: 'translateX(calc(100% + 1rem))' },
                { opacity: '1', transform: 'translateX(0)' },
            ],
            { duration: 400, easing: SPRING_IN, fill: 'forwards' },
        )
        .addEventListener('finish', done, { once: true });
}

function onLeave(el: Element, done: () => void): void {
    const h = el as HTMLElement;
    const height = h.offsetHeight;
    const mb = parseFloat(window.getComputedStyle(h).marginBottom) || 0;
    h.style.overflow = 'hidden';

    h.animate(
        [
            { opacity: '1', transform: 'translateX(0) scale(1)', height: `${height}px`, marginBottom: `${mb}px` },
            { opacity: '0', transform: 'translateX(calc(100% + 1rem)) scale(0.95)', height: '0px', marginBottom: '0px' },
        ],
        { duration: 320, easing: EASE_OUT, fill: 'forwards' },
    ).addEventListener('finish', done, { once: true });
}
</script>

<template>
    <Teleport v-if="mounted" to="body">
        <div
            class="fixed bottom-4 right-4 z-9999 flex flex-col"
            aria-live="polite"
            aria-label="Notifications"
        >
            <TransitionGroup
                :css="false"
                @before-enter="onBeforeEnter"
                @enter="onEnter"
                @leave="onLeave"
            >
                <div
                    v-for="notification in notifications"
                    :key="notification.id"
                    class="mb-2"
                >
                    <NotificationItem
                        :notification="notification"
                        @dismiss="dismiss"
                    />
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>
