import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {Locale, renderWithProviders, ValidationError} from '@akeneo-pim-community/shared';
import {LocaleDropdown} from './LocaleDropdown';

const locales: Locale[] = [
  {
    code: 'de_DE',
    label: 'German',
    region: 'Germany',
    language: 'German',
  },
  {
    code: 'en_US',
    label: 'English',
    region: 'United States',
    language: 'English',
  },
  {
    code: 'fr_FR',
    label: 'French',
    region: 'France',
    language: 'French',
  },
];

test('it displays all locales when opening the dropdown', () => {
  const handleOnChange = jest.fn();

  renderWithProviders(
    <LocaleDropdown locales={locales} value="en_US" validationErrors={[]} onChange={handleOnChange} />
  );

  userEvent.click(screen.getByLabelText('pim_common.locale'));

  expect(screen.getByText('German')).toBeInTheDocument();
  expect(screen.getByText('French')).toBeInTheDocument();
  expect(screen.getAllByText('English')).toHaveLength(2);
});

test('it calls onChange handler when selecting another locale', () => {
  const handleOnChange = jest.fn();
  renderWithProviders(
    <LocaleDropdown locales={locales} value="en_US" validationErrors={[]} onChange={handleOnChange} />
  );

  userEvent.click(screen.getByLabelText('pim_common.locale'));
  userEvent.click(screen.getByText('German'));

  expect(handleOnChange).toHaveBeenCalledWith('de_DE');
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
    <LocaleDropdown locales={locales} value="en_US" validationErrors={validationErrors} onChange={jest.fn()} />
  );

  expect(screen.getByText('error.key.an_error')).toBeInTheDocument();
  expect(screen.getByText('error.key.another_error')).toBeInTheDocument();
});
