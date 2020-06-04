import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {render, screen, fireEvent} from '@testing-library/react';
import {Panel} from 'akeneocommunicationchannel/components/panel/Panel';

const mediator = require('oro/mediator');

afterEach(() => {
  global.fetch && global.fetch.mockClear();
  delete global.fetch;
  console.error.mockClear();
});

test('it shows the panel', () => {
  render(<Panel />);

  expect(screen.getByText('akeneo_communication_channel.panel.title')).toBeInTheDocument();
});

test('it can close the panel', () => {
  render(<Panel />);

  fireEvent.click(screen.getByTitle('akeneo_communication_channel.panel.button.close'));

  expect(mediator.trigger).toHaveBeenCalledWith('communication-channel:panel:close');
})
