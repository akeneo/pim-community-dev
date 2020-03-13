import {createContext} from 'react';

export enum NotificationLevel {
    INFO = 'info',
    SUCCESS = 'success',
    WARNING = 'warning',
    ERROR = 'error',
}

export type NotifyContextValue = (level: NotificationLevel, message: string) => void;

export const NotifyContext = createContext<NotifyContextValue>(() => undefined);
