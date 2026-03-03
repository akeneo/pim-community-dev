import {ReactElement, ReactNode, useCallback, useState} from 'react';
import {IconProps, MessageBarLevel, uuid} from 'akeneo-design-system';
import {IdentifiableFlashMessage} from '../components';

const useNotifications = () => {
  const [notifications, setNotifications] = useState<IdentifiableFlashMessage[]>([]);

  const notify = useCallback(
    (level: MessageBarLevel, title: string, children?: ReactNode, icon?: ReactElement<IconProps>): void => {
      setNotifications(notifications => [...notifications, {identifier: uuid(), level, title, children, icon}]);
    },
    []
  );

  const handleNotificationClose = useCallback((identifier: string) => {
    setNotifications(notifications => notifications.filter(notification => notification.identifier !== identifier));
  }, []);

  return [notifications, notify, handleNotificationClose] as const;
};

export {useNotifications};
