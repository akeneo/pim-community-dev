import {FlashMessage} from 'akeneo-design-system';

export enum NotificationLevel {
    INFO = 'info',
    SUCCESS = 'success',
    WARNING = 'warning',
    ERROR = 'error',
}

export type Notify = (flashMessage: FlashMessage) => void;
