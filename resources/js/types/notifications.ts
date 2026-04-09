export type NotificationType = 'info' | 'success' | 'warning' | 'danger';

export interface Notification {
    id: string;
    type: NotificationType;
    title: string;
    description?: string;
    duration: number;
}

export type NotificationInput = Omit<Notification, 'id' | 'duration'> & {
    duration?: number;
};

export interface NotificationBroadcastMessage {
    type: 'notify';
    payload: NotificationInput;
}
