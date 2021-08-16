import React from 'react';
import {screen} from '@testing-library/react';
import {Channel, renderWithProviders, ValidationError} from '@akeneo-pim-community/shared';
import userEvent from '@testing-library/user-event';
import {ChannelDropdown} from './ChannelDropdown';

const channels: Channel[] = [
  {
    code: 'ecommerce',
    labels: {},
    locales: [
      {
        code: 'en_US',
        label: 'en_US',
        region: 'US',
        language: 'en',
      },
      {
        code: 'fr_FR',
        label: 'fr_FR',
        region: 'FR',
        language: 'fr',
      },
    ],
    category_tree: '',
    conversion_units: [],
    currencies: [],
    meta: {
      created: '',
      form: '',
      id: 1,
      updated: '',
    },
  },
  {
    code: 'print',
    labels: {},
    locales: [
      {
        code: 'en_US',
        label: 'en_US',
        region: 'US',
        language: 'en',
      },
      {
        code: 'fr_FR',
        label: 'fr_FR',
        region: 'FR',
        language: 'fr',
      },
    ],
    category_tree: '',
    conversion_units: [],
    currencies: [],
    meta: {
      created: '',
      form: '',
      id: 1,
      updated: '',
    },
  },
];

test('it display channel dropdown', () => {
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
