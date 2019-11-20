import * as React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {render} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
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
  const {container, getByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <AssetItem asset={asset} context={context} onRemove={onRemove} />
    </ThemeProvider>
  );

  expect(getByText(asset.code)).toBeInTheDocument();
  expect(getByText(asset.labels[context.locale])).toBeInTheDocument();
  expect(container.querySelector('button').title).toEqual('pim_asset_manager.asset_picker.basket.remove_one_asset');
});

test('It should display a placeholder when the asset is loading', () => {
  const {container} = render(
    <ThemeProvider theme={akeneoTheme}>
      <AssetItem asset={asset} context={context} onRemove={onRemove} isLoading={true} />
    </ThemeProvider>
  );

  expect(container.querySelector('li[data-loading="true"]')).toBeInTheDocument();
});
