import React from 'react';
import {MessageBar} from './MessageBar';
import {render, screen} from '../../storybook/test-util';
import {InfoIcon} from '../../icons';

test('it renders its children properly', () => {
  render(
    <MessageBar icon={<InfoIcon />} title="Title">
      MessageBar content
    </MessageBar>
  );

  expect(screen.getByText('MessageBar content')).toBeInTheDocument();
});
