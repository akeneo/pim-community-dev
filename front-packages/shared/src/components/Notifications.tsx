import React, {useCallback} from 'react';
import styled from 'styled-components';
import {AnimateMessageBar, FlashMessage, MessageBar} from 'akeneo-design-system';
import {useTranslate} from '../hooks';

const Container = styled.div`
  display: flex;
  flex-direction: column-reverse;
  position: fixed;
  bottom: 44px;
  right: 44px;
  z-index: 100000;
  gap: 10px;
`;

type IdentifiableFlashMessage = FlashMessage & {identifier: string};

type NotificationsProps = {
  notifications: IdentifiableFlashMessage[];
  onNotificationClosed: (identifier: string) => void;
};

const Notifications = ({notifications, onNotificationClosed}: NotificationsProps) => {
  const translate = useTranslate();
  const handleClose = useCallback(
    (identifier: string) => () => onNotificationClosed(identifier),
    [onNotificationClosed]
  );

  return (
    <Container>
      {notifications.map(({identifier, ...props}) => (
        <AnimateMessageBar key={identifier}>
          <MessageBar {...props} onClose={handleClose(identifier)} dismissTitle={translate('pim_common.close')} />
        </AnimateMessageBar>
      ))}
    </Container>
  );
};

export {Notifications};
export type {IdentifiableFlashMessage};
