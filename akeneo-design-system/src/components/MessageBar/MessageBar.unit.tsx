import React from 'react';
import {MessageBar} from './MessageBar';
import {render, screen} from '../../storybook/test-util';
import {InfoIcon} from '../../icons';

test('it renders its children properly', () => {
  render(
    <MessageBar level="info" icon={<InfoIcon />} title="Title">
      MessageBar Info
    </MessageBar>
  );

  render(
    <MessageBar level="success" icon={<InfoIcon />} title="Title">
      MessageBar Success
    </MessageBar>
  );

  render(
    <MessageBar level="warning" icon={<InfoIcon />} title="Title">
      MessageBar Warning
    </MessageBar>
  );

  render(
    <MessageBar level="danger" icon={<InfoIcon />} title="Title">
      MessageBar Danger
    </MessageBar>
  );

  expect(screen.getByText('MessageBar Info')).toBeInTheDocument();
  expect(screen.getByText('MessageBar Success')).toBeInTheDocument();
  expect(screen.getByText('MessageBar Warning')).toBeInTheDocument();
  expect(screen.getByText('MessageBar Danger')).toBeInTheDocument();
});
