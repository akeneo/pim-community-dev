import React, {ReactElement, ReactNode, useCallback} from 'react';
import ReactDOM from 'react-dom';
import styled, {ThemeProvider} from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {DependenciesProvider} from '@akeneo-pim-community/shared';
import {
  AnimateMessageBar,
  FlashMessage,
  IconProps,
  MessageBar,
  MessageBarLevel,
  pimTheme,
  uuid,
} from 'akeneo-design-system';

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

const Notifications = ({
  notifications,
  onNotificationClosed,
}: {
  notifications: IdentifiableFlashMessage[];
  onNotificationClosed: (identifier: string) => void;
}) => {
  const handleClose = useCallback((identifier: string) => () => onNotificationClosed(identifier), []);
  const translate = useTranslate();

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

let notifications: IdentifiableFlashMessage[] = [];

const render = () =>
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

const isValidLevel = (level: string): level is MessageBarLevel =>
  ['info', 'error', 'warning', 'success', undefined].includes(level);

interface Notifier {
  (level: MessageBarLevel, title: string, children?: ReactNode, icon?: ReactElement<IconProps>): void;

  /**
   * @deprecated('This version of notify should not be used. Prefer notify(flashMessage)')
   */
  (level: string, message: string, options?: {titleMessage: string}): void;
}

const isLegacyNotify = (
  optionsOrChildren: {titleMessage: string} | ReactNode
): optionsOrChildren is {titleMessage: string} =>
  typeof optionsOrChildren === 'object' &&
  null !== optionsOrChildren &&
  ('titleMessage' in optionsOrChildren || 'flash' in optionsOrChildren);

const notify: Notifier = (
  level: MessageBarLevel | string,
  messageOrTitle: string,
  optionsOrChildren?: {titleMessage: string} | ReactNode,
  icon?: ReactElement<IconProps>
): void => {
  if (!isValidLevel(level)) {
    throw new Error(`Level must be one of the following: 'info', 'error', 'warning' or 'success'`);
  }

  const flashMessage: FlashMessage = isLegacyNotify(optionsOrChildren)
    ? convertLegacyFlashMessage(level, messageOrTitle, optionsOrChildren)
    : {
        level,
        title: messageOrTitle,
        children: optionsOrChildren,
        icon,
      };

  notifications.push({identifier: uuid(), ...flashMessage});
  render();
};

const convertLegacyFlashMessage = (
  level: MessageBarLevel,
  message?: string,
  options?: {titleMessage: string}
): FlashMessage => {
  if (undefined === message) {
    throw new Error('message property is required in the notify method');
  }

  return {
    level,
    title: options?.titleMessage ?? message,
    children: options?.titleMessage ? message : null,
  };
};

module.exports = {notify};
