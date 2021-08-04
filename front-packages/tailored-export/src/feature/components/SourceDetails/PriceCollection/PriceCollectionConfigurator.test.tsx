import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {PriceCollectionConfigurator} from './PriceCollectionConfigurator';
import {PriceCollectionSelection, getDefaultPriceCollectionSource} from './model';
import {getDefaultDateSource} from '../Date/model';

const attribute = {
  code: 'price_collection',
  type: 'pim_catalog_price',
  labels: {},
  scopable: false,
  localizable: false,
  is_locale_specific: false,
  available_locales: [],
};

jest.mock('./PriceCollectionSelector', () => ({
  PriceCollectionSelector: ({
    onSelectionChange,
  }: {
    onSelectionChange: (updatedSelection: PriceCollectionSelection) => void;
  }) => (
    <button
      onClick={() =>
        onSelectionChange({
          type: 'currency_code',
          separator: ';',
        })
      }
    >
      Update selection
    </button>
  ),
}));

test('it displays a price collection configurator', () => {
  const onSourceChange = jest.fn();

  renderWithProviders(
    <PriceCollectionConfigurator
      source={{
        ...getDefaultPriceCollectionSource(attribute, null, null),
        uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
      }}
      attribute={attribute}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  userEvent.click(screen.getByText('Update selection'));

  expect(onSourceChange).toHaveBeenCalledWith({
    ...getDefaultPriceCollectionSource(attribute, null, null),
    selection: {
      type: 'currency_code',
      separator: ';',
    },
    uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
  });
});

test('it does not render if the source is not valid', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
  const onSourceChange = jest.fn();

  const dateAttribute = {...attribute, type: 'pim_catalog_date', code: 'date_attribute'};

  expect(() => {
    renderWithProviders(
      <PriceCollectionConfigurator
        source={getDefaultDateSource(dateAttribute, null, null)}
        attribute={dateAttribute}
        validationErrors={[]}
        onSourceChange={onSourceChange}
      />
    );
  }).toThrow('Invalid source data "date_attribute" for price collection configurator');

  expect(screen.queryByText('Update selection')).not.toBeInTheDocument();
  mockedConsole.mockRestore();
});
