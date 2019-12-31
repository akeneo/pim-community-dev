import * as React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {render, fireEvent} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import AssetCard from 'akeneoassetmanager/application/component/asset/list/mosaic/asset-card';

const asset = {
  code: 'iphone',
  labels: {
    en_US: 'iPhone X',
  },
  image: [{attribute: 'nice', locale: null, channel: null, data: {filePath: 'my_image_url', originalFilename: ''}}],
  completeness: {},
};

test('It displays an unselected asset card', () => {
  const isSelected = false;
  const {getByText, container} = render(
    <ThemeProvider theme={akeneoTheme}>
      <AssetCard
        asset={asset}
        context={{locale: 'en_US', channel: 'ecommerce'}}
        isSelected={isSelected}
        onSelectionChange={() => {}}
      />
    </ThemeProvider>
  );
  expect(container.querySelector('img').src).toEqual('');
  expect(container.querySelector('[data-checked="false"]')).toBeInTheDocument();
  expect(getByText(asset.labels.en_US)).toBeInTheDocument();
});

test('It displays selected asset card', () => {
  const isSelected = true;
  const {getByText, container} = render(
    <ThemeProvider theme={akeneoTheme}>
      <AssetCard
        asset={asset}
        context={{locale: 'en_US', channel: 'ecommerce'}}
        isSelected={isSelected}
        onSelectionChange={() => {}}
      />
    </ThemeProvider>
  );
  expect(container.querySelector('img').src).toEqual('');
  expect(container.querySelector('[data-checked="true"]')).toBeInTheDocument();
  expect(getByText(asset.labels.en_US)).toBeInTheDocument();
});

test('it can be selected when clicking on the checkbox', () => {
  let isSelected = false;
  let selectedCode = null;
  const {container} = render(
    <ThemeProvider theme={akeneoTheme}>
      <AssetCard
        asset={asset}
        context={{locale: 'en_US', channel: 'ecommerce'}}
        isSelected={isSelected}
        onSelectionChange={(code, value) => {
          isSelected = value;
          selectedCode = code;
        }}
      />
    </ThemeProvider>
  );

  fireEvent.click(container.querySelector('[data-checked]'));

  expect(isSelected).toEqual(true);
  expect(selectedCode).toEqual(asset.code);
});

test('it can be selected when clicking on the asset card', () => {
  let isSelected = false;
  let selectedCode = null;
  const {container} = render(
    <ThemeProvider theme={akeneoTheme}>
      <AssetCard
        asset={asset}
        context={{locale: 'en_US', channel: 'ecommerce'}}
        isSelected={isSelected}
        onSelectionChange={(code, value) => {
          isSelected = value;
          selectedCode = code;
        }}
      />
    </ThemeProvider>
  );

  fireEvent.click(container.querySelector('img'));

  expect(isSelected).toEqual(true);
  expect(selectedCode).toEqual(asset.code);
});
