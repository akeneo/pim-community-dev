import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import AssetItem from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-picker/basket/asset-item';

const asset = {
  code: 'iphone',
  labels: {
    en_US: 'iPhone X',
  },
  image: [{attribute: 'nice', locale: null, channel: null, data: {filePath: 'my_image_url', originalFilename: ''}}],
};

const context = {
  channel: 'ecommerce',
  locale: 'en_US',
};

const onRemove = jest.fn();

test('It should display an item', () => {
  renderWithProviders(<AssetItem asset={asset} context={context} onRemove={onRemove} />);

  expect(screen.getByText(asset.code)).toBeInTheDocument();
  expect(screen.getByText(asset.labels[context.locale])).toBeInTheDocument();
  expect(screen.getByTitle('pim_asset_manager.asset_picker.basket.remove_one_asset')).toBeInTheDocument();
});

test('It should display a placeholder when the asset is loading', () => {
  const {container} = renderWithProviders(
    <AssetItem asset={asset} context={context} onRemove={onRemove} isLoading={true} />
  );

  expect(container.querySelector('li[data-loading="true"]')).toBeInTheDocument();
});
