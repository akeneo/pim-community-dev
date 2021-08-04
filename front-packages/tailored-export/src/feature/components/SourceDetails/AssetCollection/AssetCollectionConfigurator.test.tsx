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

test('it does not render if the source is not valid', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
  const onSourceChange = jest.fn();

  expect(() => {
    renderWithProviders(
      <AssetCollectionConfigurator
        source={getDefaultTextSource(attribute, null, null)}
        attribute={attribute}
        validationErrors={[]}
        onSourceChange={onSourceChange}
      />
    );
  }).toThrow('Invalid source data "asset" for asset collection configurator');

  expect(screen.queryByText('Update selection')).not.toBeInTheDocument();
  mockedConsole.mockRestore();
});
