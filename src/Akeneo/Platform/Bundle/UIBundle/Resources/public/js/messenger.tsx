import ReactDOM from 'react-dom';
import React, {useCallback} from 'react';
import {AnimateMessageBar, MessageBar, FlashMessage, MessageBarLevel, pimTheme, uuid} from 'akeneo-design-system';
import styled, {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';

const Container = styled.div`
  display: flex;
  flex-direction: column-reverse;
  position: fixed;
  bottom: 40px;
  right: 40px;
  z-index: 100000;
  gap: 10px;
`;

type IdentifiableFlashMessage = FlashMessage & {identifier: string};

const Notifications = ({
  notifications,
  onNotificationClosed,
}: {
  notifications: IdentifiableFlashMessage[];
  onNotificationClosed: (identifier: string) => void;
}) => {
  const handleClose = useCallback(
    (identifier: string) => () => {
      onNotificationClosed(identifier);
    },
    []
  );

  return (
    <Container>
      {notifications.map(({identifier, ...props}) => (
        <AnimateMessageBar key={identifier}>
          <MessageBar {...props} onClose={handleClose(identifier)} />
        </AnimateMessageBar>
      ))}
    </Container>
  );
};

let notifications: IdentifiableFlashMessage[] = [];

const render = () => {
  ReactDOM.render(
    <ThemeProvider theme={pimTheme}>
      <DependenciesProvider>
        <Notifications
          notifications={notifications}
          onNotificationClosed={(identifier: string) => {
            notifications = notifications.filter(notification => notification.identifier !== identifier);
            render();
          }}
        />
      </DependenciesProvider>
    </ThemeProvider>,
    document.getElementById('flash-messages')
  );
};

const isValidLevel = (level: string): level is MessageBarLevel =>
  ['info', 'error', 'warning', 'success', undefined].includes(level)


const isValidFlashMessage = (flash: any): flash is FlashMessage => isValidLevel(flash.level);

interface Notifier {
  (flashMessage: FlashMessage): void;

  /**
   * @deprecated('This version of notify should not be used. Prefer notify(flashMessage)')
   */
  (level: MessageBarLevel, message?: string, options?: {titleMessage: string}): void;
}

const notify: Notifier = (
  flashMessageOrLevel: FlashMessage|string,
  message?: string,
  options?: {titleMessage: string}
): void => {
  const flashMessage: FlashMessage = typeof flashMessageOrLevel === 'string' ?
    convertLegacyFlashMessage(flashMessageOrLevel, message, options) :
    flashMessageOrLevel;

  if (!isValidFlashMessage(flashMessage)) {
    throw new Error(`Flash message must be a valid FlashMessage, received: ${JSON.stringify(flashMessage)}`);
  }

  notifications.push({identifier: uuid(), ...flashMessage});
  render();
};

const convertLegacyFlashMessage = (level: string, message?: string, options?: {titleMessage: string}): FlashMessage => {
  if (undefined === message) {
    throw new Error('message property is required in the notify method');
  }

  if (!isValidLevel(level)) {
    throw new Error(`Level must be one of the following: 'info', 'error', 'warning' or 'success'`);
  }

  return {
    level,
    title: options?.titleMessage ?? message,
    children: options?.titleMessage ? message : null
  }
}

module.exports = {notify};
