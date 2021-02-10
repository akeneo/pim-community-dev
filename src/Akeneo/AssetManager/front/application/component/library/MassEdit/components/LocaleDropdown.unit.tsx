import React from 'react';
import {screen, fireEvent} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import Locale from 'akeneoassetmanager/domain/model/locale';
import {LocaleDropdown} from './LocaleDropdown';

const locales: Locale[] = [
  {
    "code": "de_DE",
    "label": "German (Germany)",
    "region": "Germany",
    "language": "German"
  },
  {
    "code": "en_US",
    "label": "English (United States)",
    "region": "United States",
    "language": "English"
  },
  {
    "code": "fr_FR",
    "label": "French (France)",
    "region": "France",
    "language": "French"
  }
];

test('it renders its children properly', () => {
  renderWithProviders(
    <LocaleDropdown
      readOnly={false}
      locales={locales}
      locale="en_US"
      onChange={() => {}}
    />
  );

  expect(screen.getByText('English')).toBeInTheDocument();
});

test('it display all locales when clicking on the dropdown', () => {
  const handleOnChange = jest.fn();
  renderWithProviders(
    <LocaleDropdown
      readOnly={false}
      locales={locales}
      locale="en_US"
      onChange={handleOnChange}
    />
  );

  const dropdownButton = screen.getByText('English');
  fireEvent.click(dropdownButton);

  expect(screen.getAllByText('English').length).toEqual(2);
  expect(screen.getByText('German')).toBeInTheDocument();
  expect(screen.getByText('French')).toBeInTheDocument();
});

test('it does not display the dropdown when read only', () => {
  const handleOnChange = jest.fn();
  renderWithProviders(
    <LocaleDropdown
      readOnly={true}
      locales={locales}
      locale="en_US"
      onChange={handleOnChange}
    />
  );

  const dropdownButton = screen.getByText('English');
  fireEvent.click(dropdownButton);

  expect(screen.queryByText('German')).not.toBeInTheDocument();
  expect(screen.queryByText('French')).not.toBeInTheDocument();
});

test('it call onChange handler when user click on another locale', () => {
  const handleOnChange = jest.fn();
  renderWithProviders(
    <LocaleDropdown
      readOnly={false}
      locales={locales}
      locale="en_US"
      onChange={handleOnChange}
    />
  );

  const dropdownButton = screen.getByText('English');
  fireEvent.click(dropdownButton);
  const newOption = screen.getByText('German');
  fireEvent.click(newOption);

  expect(handleOnChange).toHaveBeenCalledWith('de_DE')
});
