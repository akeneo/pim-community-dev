import {useApplicationContext} from './useApplicationContext';
import {Notify} from '../provider/applicationDependenciesProvider.type';

enum NotificationLevel {
  INFO = 'info',
  SUCCESS = 'success',
  WARNING = 'warning',
  ERROR = 'error',
}

const useNotify = (): Notify => {
  const {notify} = useApplicationContext();
  if (notify) {
    return notify;
  }
  throw new Error(
    '[ApplicationContext]: Notify has not been properly initiated'
  );
};

export {useNotify, NotificationLevel};
