import React from 'react';
import {screen, fireEvent} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import Channel from 'akeneoassetmanager/domain/model/channel';
import {ChannelDropdown} from './ChannelDropdown';

const channels: Channel[] = [
  {
    "code": "ecommerce",
    "locales": [
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
    ],
    "labels": {
      "en_US": "Ecommerce",
      "de_DE": "Ecommerce",
      "fr_FR": "Ecommerce"
    },
  },
  {
    "code": "mobile",
    "locales": [
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
    ],
    "labels": {
      "en_US": "Mobile",
      "de_DE": "Mobil",
      "fr_FR": "Mobile"
    }
  },
  {
    "code": "print",
    "locales": [
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
    ],
    "labels": {
      "en_US": "Print",
      "de_DE": "Drucken",
      "fr_FR": "Impression"
    },
  }
];

test('it renders its children properly', () => {
  renderWithProviders(
    <ChannelDropdown
      readOnly={false}
      uiLocale="en_US"
      channels={channels}
      channel="ecommerce"
      onChange={() => {}}
    />
  );

  expect(screen.getByText('Ecommerce')).toBeInTheDocument();
});

test('it display all channels when clicking on the dropdown', () => {
  const handleOnChange = jest.fn();
  renderWithProviders(
    <ChannelDropdown
      readOnly={false}
      uiLocale="en_US"
      channels={channels}
      channel="ecommerce"
      onChange={handleOnChange}
    />
  );

  const dropdownButton = screen.getByText('Ecommerce');
  fireEvent.click(dropdownButton);

  expect(screen.getAllByText('Ecommerce').length).toEqual(2);
  expect(screen.getByText('Mobile')).toBeInTheDocument();
  expect(screen.getByText('Print')).toBeInTheDocument();
});

test('it does not display the dropdown when read only', () => {
  const handleOnChange = jest.fn();
  renderWithProviders(
    <ChannelDropdown
      readOnly={true}
      uiLocale="en_US"
      channels={channels}
      channel="ecommerce"
      onChange={handleOnChange}
    />
  );

  const dropdownButton = screen.getByText('Ecommerce');
  fireEvent.click(dropdownButton);

  expect(screen.queryByText('Mobile')).not.toBeInTheDocument();
  expect(screen.queryByText('Print')).not.toBeInTheDocument();
});

test('it call onChange handler when user click on another channel', () => {
  const handleOnChange = jest.fn();
  renderWithProviders(
    <ChannelDropdown
      readOnly={false}
      uiLocale="en_US"
      channels={channels}
      channel="ecommerce"
      onChange={handleOnChange}
    />
  );

  const dropdownButton = screen.getByText('Ecommerce');
  fireEvent.click(dropdownButton);
  const newOption = screen.getByText('Mobile');
  fireEvent.click(newOption);

  expect(handleOnChange).toHaveBeenCalledWith('mobile')
});
