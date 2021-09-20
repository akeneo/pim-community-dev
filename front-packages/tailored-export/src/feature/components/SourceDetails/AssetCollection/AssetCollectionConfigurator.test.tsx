import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {AssetCollectionConfigurator} from './AssetCollectionConfigurator';
import {getDefaultTextSource} from '../Text/model';
import {getDefaultAssetCollectionSource} from './model';

const attribute = {
  code: 'asset',
  type: 'pim_catalog_asset_collection',
  labels: {},
  scopable: false,
  localizable: false,
  is_locale_specific: false,
  available_locales: [],
  reference_data_name: 'asset_family',
};

jest.mock('./AssetCollectionSelector');
jest.mock('../common/DefaultValue');

describe('it displays an asset collection configurator', () => {
  test('it can update default value operation', () => {
    const onSourceChange = jest.fn();

    renderWithProviders(
      <AssetCollectionConfigurator
        source={{
          ...getDefaultAssetCollectionSource(attribute, null, null),
          uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
        }}
        attribute={attribute}
        validationErrors={[]}
        onSourceChange={onSourceChange}
      />
    );

    userEvent.click(screen.getByText('Default value'));

    expect(onSourceChange).toHaveBeenCalledWith({
      ...getDefaultAssetCollectionSource(attribute, null, null),
      operations: {
        default_value: {
          type: 'default_value',
          value: 'foo',
        },
      },
      uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
    });
  });

  test('it can update asset collection selector', () => {
    const onSourceChange = jest.fn();

    renderWithProviders(
      <AssetCollectionConfigurator
        source={{
          ...getDefaultAssetCollectionSource(attribute, null, null),
          uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
        }}
        attribute={attribute}
        validationErrors={[]}
        onSourceChange={onSourceChange}
      />
    );

    userEvent.click(screen.getByText('Asset collection selector'));

    expect(onSourceChange).toHaveBeenCalledWith({
      ...getDefaultAssetCollectionSource(attribute, null, null),
      selection: {
        type: 'label',
        locale: 'en_US',
        separator: ',',
      },
      uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
    });
  });
});

test('it tells when the source data is invalid', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
  const dateAttribute = {...attribute, type: 'pim_catalog_date', code: 'date_attribute'};

  expect(() => {
    renderWithProviders(
      <AssetCollectionConfigurator
        source={getDefaultTextSource(dateAttribute, null, null)}
        attribute={dateAttribute}
        validationErrors={[]}
        onSourceChange={jest.fn()}
      />
    );
  }).toThrow('Invalid source data "date_attribute" for asset collection configurator');

  expect(screen.queryByText('Asset collection selector')).not.toBeInTheDocument();
  mockedConsole.mockRestore();
});

test('it tells when the attribute is invalid', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
  const invalidAttribute = {...attribute, reference_data_name: undefined};

  expect(() => {
    renderWithProviders(
      <AssetCollectionConfigurator
        source={{
          ...getDefaultAssetCollectionSource(attribute, null, null),
          uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
        }}
        attribute={invalidAttribute}
        validationErrors={[]}
        onSourceChange={jest.fn()}
      />
    );
  }).toThrow('Asset collection attribute "asset" should have a reference_data_name');

  expect(screen.queryByText('Update selection')).not.toBeInTheDocument();
  mockedConsole.mockRestore();
});
