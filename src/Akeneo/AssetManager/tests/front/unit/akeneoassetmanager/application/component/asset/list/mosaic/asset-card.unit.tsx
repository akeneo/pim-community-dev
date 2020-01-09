import * as React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {render, fireEvent, act} from '@testing-library/react';
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
jest.mock('akeneoassetmanager/tools/image-loader', () =>
  jest.fn().mockImplementation(
    url =>
      new Promise(resolve => {
        act(() => resolve());
      })
  )
);

test('It displays an unselected asset card', async () => {
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

  await setTimeout(() => new Promise(resolve => resolve), 10);

  expect(container.querySelector('img').src).toEqual('');
  expect(container.querySelector('[data-checked="false"]')).toBeInTheDocument();
  expect(getByText(asset.labels.en_US)).toBeInTheDocument();
});

test('It displays selected asset card', async () => {
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

  await setTimeout(() => new Promise(resolve => resolve), 10);

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

  act(() => {
    fireEvent.click(container.querySelector('[data-checked]'));
  });

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

  act(() => {
    fireEvent.click(container.querySelector('[data-test-id="asset-card-image"]'));
  });

  expect(isSelected).toEqual(true);
  expect(selectedCode).toEqual(asset.code);
});

test('it cannot be selected if there is an on change property', () => {
  let isSelected = false;
  let selectedCode = null;
  const {container} = render(
    <ThemeProvider theme={akeneoTheme}>
      <AssetCard
        asset={asset}
        context={{locale: 'en_US', channel: 'ecommerce'}}
        isSelected={isSelected}
        onClick={() => {}}
        onSelectionChange={(code, value) => {
          isSelected = value;
          selectedCode = code;
        }}
      />
    </ThemeProvider>
  );
  act(() => {
    fireEvent.click(container.firstElementChild);
  });

  expect(isSelected).toEqual(false);
  expect(selectedCode).toEqual(null);
});
