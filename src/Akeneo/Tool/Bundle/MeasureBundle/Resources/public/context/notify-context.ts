import {createContext} from 'react';

enum NotificationLevel {
  INFO = 'info',
  SUCCESS = 'success',
  WARNING = 'warning',
  ERROR = 'error',
}

type NotifyContextValue = (level: NotificationLevel, message: string) => void;

const NotifyContext = createContext<NotifyContextValue>(() => undefined);

export {NotificationLevel, NotifyContextValue, NotifyContext};
