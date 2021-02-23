import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import Locale from 'akeneoassetmanager/domain/model/locale';
import {LocaleDropdown} from './LocaleDropdown';

const locales: Locale[] = [
  {
    code: 'de_DE',
    label: 'German (Germany)',
    region: 'Germany',
    language: 'German',
  },
  {
    code: 'en_US',
    label: 'English (United States)',
    region: 'United States',
    language: 'English',
  },
  {
    code: 'fr_FR',
    label: 'French (France)',
    region: 'France',
    language: 'French',
  },
];

test('it renders its children properly', () => {
  renderWithProviders(<LocaleDropdown locales={locales} locale="en_US" onChange={jest.fn()} />);

  expect(screen.getByText('English')).toBeInTheDocument();
});

test('it displays all locales when opening the dropdown', () => {
  const handleOnChange = jest.fn();
  renderWithProviders(<LocaleDropdown locales={locales} locale="en_US" onChange={handleOnChange} />);

  userEvent.click(screen.getByRole('textbox'));

  expect(screen.getAllByText('English')).toHaveLength(2);
  expect(screen.getByText('German')).toBeInTheDocument();
  expect(screen.getByText('French')).toBeInTheDocument();
});

test('it does not display the dropdown when read only', () => {
  const handleOnChange = jest.fn();
  renderWithProviders(<LocaleDropdown readOnly={true} locales={locales} locale="en_US" onChange={handleOnChange} />);

  userEvent.click(screen.getByRole('textbox'));

  expect(screen.queryByText('German')).not.toBeInTheDocument();
  expect(screen.queryByText('French')).not.toBeInTheDocument();
});

test('it calls onChange handler when selecting another locale', () => {
  const handleOnChange = jest.fn();
  renderWithProviders(<LocaleDropdown locales={locales} locale="en_US" onChange={handleOnChange} />);

  userEvent.click(screen.getByRole('textbox'));
  userEvent.click(screen.getByText('German'));

  expect(handleOnChange).toHaveBeenCalledWith('de_DE');
});

test('it returns nothing when locale is not found', () => {
  renderWithProviders(<LocaleDropdown locales={locales} locale="unknown_locale" onChange={jest.fn()} />);

  expect(screen.queryByText('unknown_locale')).not.toBeInTheDocument();
});
