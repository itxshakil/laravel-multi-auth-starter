import { usePage } from '@inertiajs/vue3';
import { reactive, watch } from 'vue';
import type { Notification, NotificationInput, NotificationBroadcastMessage } from '@/types/notifications';

const BROADCAST_CHANNEL_NAME = 'app-notifications';

function computeDuration(input: NotificationInput): number {
    if (input.duration !== undefined) {
        return input.duration;
    }

    const text = [input.title, input.description].filter(Boolean).join(' ');
    const wordCount = text.trim().split(/\s+/).filter(Boolean).length;

    // ~200 ms per word, 2 s base, capped between 3 s and 8 s
    return Math.max(3000, Math.min(8000, 2000 + wordCount * 200));
}

// --- Module-level singleton ---
// Shared by all importers; created once when the module first loads.
const notifications = reactive<Notification[]>([]);

export function notify(input: NotificationInput): void {
    const id =
        typeof crypto.randomUUID === 'function'
            ? crypto.randomUUID()
            : `${Date.now().toString(36)}-${Math.random().toString(36).slice(2)}`;

    const duration = computeDuration(input);

    notifications.push({ ...input, id, duration });

    if (duration > 0) {
        setTimeout(() => dismiss(id), duration);
    }
}

export function dismiss(id: string): void {
    const index = notifications.findIndex((n) => n.id === id);

    if (index !== -1) {
        notifications.splice(index, 1);
    }
}

export function broadcastNotify(input: NotificationInput): void {
    if (typeof window === 'undefined') {
        return;
    }

    const channel = new BroadcastChannel(BROADCAST_CHANNEL_NAME);
    const message: NotificationBroadcastMessage = { type: 'notify', payload: input };
    channel.postMessage(message);
    channel.close();
}

function dispatchFlash(props: Record<string, unknown>): void {
    if (typeof props.success === 'string') {
        notify({ type: 'success', title: props.success });
    }

    if (typeof props.warning === 'string') {
        notify({ type: 'warning', title: props.warning });
    }

    if (typeof props.info === 'string') {
        notify({ type: 'info', title: props.info });
    }

    if (typeof props.error === 'string') {
        notify({ type: 'danger', title: props.error });
    }
}

// --- Browser-only side effects ---
if (typeof window !== 'undefined') {
    // BroadcastChannel: receive notifications from other tabs / contexts
    const channel = new BroadcastChannel(BROADCAST_CHANNEL_NAME);
    channel.addEventListener('message', (event: MessageEvent<NotificationBroadcastMessage>) => {
        if (event.data?.type === 'notify') {
            notify(event.data.payload);
        }
    });

}

// useFlashNotifications must be called inside a Vue component setup so that
// usePage() and watch() have access to the correct injection context.
// Call it once in each persistent layout (AppLayout, AuthLayout).
export function useFlashNotifications(): void {
    const page = usePage();

    // immediate: true handles both initial page load and every SPA navigation.
    watch(
        () => page.props as Record<string, unknown>,
        (props) => dispatchFlash(props),
        { immediate: true },
    );
}

export function useNotifications() {
    return { notifications, notify, dismiss };
}
