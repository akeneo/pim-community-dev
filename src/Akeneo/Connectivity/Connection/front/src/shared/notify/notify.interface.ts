export enum NotificationLevel {
    INFO = 'info',
    SUCCESS = 'success',
    WARNING = 'warning',
    ERROR = 'error',
}

export type Notify = (level: NotificationLevel, message: string) => void;
