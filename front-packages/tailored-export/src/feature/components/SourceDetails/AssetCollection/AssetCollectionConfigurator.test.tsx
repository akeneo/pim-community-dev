import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {AssetCollectionConfigurator} from './AssetCollectionConfigurator';
import {getDefaultTextSource} from '../Text/model';
import {CodeLabelCollectionSelection} from '../common/CodeLabelCollectionSelector';

const attribute = {
  code: 'asset',
  type: 'pim_catalog_asset_collection',
  labels: {},
  scopable: false,
  localizable: false,
  is_locale_specific: false,
  available_locales: [],
};

jest.mock('../common/CodeLabelCollectionSelector', () => ({
  ...jest.requireActual('../common/CodeLabelCollectionSelector'),
  CodeLabelCollectionSelector: ({
    onSelectionChange,
  }: {
    onSelectionChange: (updatedSelection: CodeLabelCollectionSelection) => void;
  }) => (
    <button
      onClick={() =>
        onSelectionChange({
          type: 'label',
          locale: 'en_US',
          separator: ',',
        })
      }
    >
      Update selection
    </button>
  ),
}));

test('it displays an asset collection configurator', () => {
  const onSourceChange = jest.fn();

  renderWithProviders(
    <AssetCollectionConfigurator
      source={{
        channel: null,
        code: 'asset',
        locale: null,
        operations: {},
        selection: {
          separator: ',',
          type: 'code',
        },
        type: 'attribute',
        uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
      }}
      attribute={attribute}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  userEvent.click(screen.getByText('Update selection'));

  expect(onSourceChange).toHaveBeenCalledWith({
    channel: null,
    code: 'asset',
    locale: null,
    operations: {},
    selection: {
      locale: 'en_US',
      separator: ',',
      type: 'label',
    },
    type: 'attribute',
    uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
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

  expect(screen.queryByText('Update selection')).not.toBeInTheDocument();
  mockedConsole.mockRestore();
});
