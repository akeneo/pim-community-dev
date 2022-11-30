import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders, ValidationError} from '@akeneo-pim-community/shared';
import userEvent from '@testing-library/user-event';
import {ChannelDropdown} from './ChannelDropdown';
import {channels} from 'feature/tests';

test('it displays channel dropdown', () => {
  renderWithProviders(
    <ChannelDropdown value="ecommerce" channels={channels} validationErrors={[]} onChange={jest.fn()} />
  );

  expect(screen.getByLabelText(/pim_common.channel/i)).toBeInTheDocument();
  expect(screen.getByText('[ecommerce]')).toBeInTheDocument();
});

test('it calls handler when user click on channel', () => {
  const handleChange = jest.fn();
  renderWithProviders(
    <ChannelDropdown value="ecommerce" channels={channels} validationErrors={[]} onChange={handleChange} />
  );

  userEvent.click(screen.getByLabelText(/pim_common.channel/i));
  userEvent.click(screen.getByText('[print]'));

  expect(handleChange).toHaveBeenCalledWith('print');
});

test('it displays validation errors', () => {
  const validationErrors: ValidationError[] = [
    {
      messageTemplate: 'error.key.an_error',
      invalidValue: '',
      message: 'this is an error',
      parameters: {},
      propertyPath: '',
    },
    {
      messageTemplate: 'error.key.another_error',
      invalidValue: '',
      message: 'this is another error',
      parameters: {},
      propertyPath: '',
    },
  ];

  renderWithProviders(
    <ChannelDropdown value="ecommerce" channels={channels} validationErrors={validationErrors} onChange={jest.fn()} />
  );

  expect(screen.getByText('error.key.an_error')).toBeInTheDocument();
  expect(screen.getByText('error.key.another_error')).toBeInTheDocument();
});
