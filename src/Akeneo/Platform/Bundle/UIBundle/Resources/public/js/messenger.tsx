import ReactDOM from 'react-dom';
import React, {useCallback} from 'react';
import {AnimateMessageBar, MessageBar, FlashMessage, pimTheme, uuid} from 'akeneo-design-system';
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

const notify = (flashMessage: FlashMessage) => {
  notifications.push({identifier: uuid(), ...flashMessage});
  render();
};

module.exports = {notify};
