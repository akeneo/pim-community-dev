import * as React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {render, fireEvent} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import {Thumbnail} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-collection/thumbnail';

const labels = {en_US: 'Nice Label'};
const assetFamily = {
  identifier: 'assetFamilyIdentifier',
};

const sideViewAsset = {
  code: 'sideview',
  labels,
  image: [],
  assetFamily,
};
const asset1 = {
  identifier: 'packshot_samsung_fingerprint',
  code: 'samsung',
  labels,
  image: [],
  assetFamily,
};
const asset2 = {
  identifier: 'packshot_oneplus_fingerprint',
  code: 'oneplus',
  labels,
  image: [],
  assetFamily,
};
const asset3 = {
  identifier: 'packshot_iphone_fingerprint',
  code: 'iphone',
  labels,
  image: [],
  assetFamily,
};
const asset4 = {
  identifier: 'packshot_huawei_fingerprint',
  code: 'huawei',
  labels,
  image: [],
  assetFamily,
};
const assets = [asset1, asset2, sideViewAsset, asset3, asset4];

test('It render a thumbnail', () => {
  const {getByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <Thumbnail
        asset={sideViewAsset}
        context={{
          locale: 'en_US',
          channel: 'ecommerce',
        }}
        readonly={false}
        assetCollection={assets}
        onRemove={() => {}}
        onMove={() => {}}
      />
    </ThemeProvider>
  );
  expect(getByText('pim_asset_manager.asset_collection.remove_asset')).toBeInTheDocument();
  expect(getByText('Left')).toBeInTheDocument();
  expect(getByText('Right')).toBeInTheDocument();
});
test('It render the first thumbnail of a collection', () => {
  const {getByText, queryByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <Thumbnail
        asset={asset1}
        context={{
          locale: 'en_US',
          channel: 'ecommerce',
        }}
        readonly={false}
        assetCollection={assets}
        onRemove={() => {}}
        onMove={() => {}}
      />
    </ThemeProvider>
  );
  expect(getByText('pim_asset_manager.asset_collection.remove_asset')).toBeInTheDocument();
  expect(queryByText('Left')).not.toBeInTheDocument();
  expect(getByText('Right')).toBeInTheDocument();
});
test('It render the last thumbnail of a collection', () => {
  const {getByText, queryByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <Thumbnail
        asset={asset4}
        context={{
          locale: 'en_US',
          channel: 'ecommerce',
        }}
        readonly={false}
        assetCollection={assets}
        onRemove={() => {}}
        onMove={() => {}}
      />
    </ThemeProvider>
  );
  expect(getByText('pim_asset_manager.asset_collection.remove_asset')).toBeInTheDocument();
  expect(queryByText('Right')).not.toBeInTheDocument();
  expect(getByText('Left')).toBeInTheDocument();
});

test('It render a readonly thumbnail', () => {
  const {queryByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <Thumbnail
        asset={sideViewAsset}
        context={{
          locale: 'en_US',
          channel: 'ecommerce',
        }}
        readonly={true}
        assetCollection={assets}
        onRemove={() => {}}
        onMove={() => {}}
      />
    </ThemeProvider>
  );
  expect(queryByText('pim_asset_manager.asset_collection.remove_asset')).not.toBeInTheDocument();
  expect(queryByText('Left')).not.toBeInTheDocument();
  expect(queryByText('Right')).not.toBeInTheDocument();
});

test('It trigger event on remove asset by clicking', () => {
  let isRemoved = false;
  const {getByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <Thumbnail
        asset={sideViewAsset}
        context={{
          locale: 'en_US',
          channel: 'ecommerce',
        }}
        readonly={false}
        assetCollection={assets}
        onRemove={() => {
          isRemoved = true;
        }}
        onMove={() => {}}
      />
    </ThemeProvider>
  );

  fireEvent.click(getByText('pim_asset_manager.asset_collection.remove_asset'));
  expect(isRemoved).toEqual(true);
});

test('It trigger event on move asset left by clicking', () => {
  let isMovedLeft = false;
  let isMovedRight = false;
  const {getByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <Thumbnail
        asset={sideViewAsset}
        context={{
          locale: 'en_US',
          channel: 'ecommerce',
        }}
        readonly={false}
        assetCollection={assets}
        onRemove={() => {}}
        onMove={direction => {
          if (direction === 0) isMovedLeft = true;
          if (direction === 1) isMovedRight = true;
        }}
      />
    </ThemeProvider>
  );

  fireEvent.click(getByText('Left'));
  expect(isMovedLeft).toEqual(true);
  expect(isMovedRight).toEqual(false);
});

test('It trigger event on move asset right by clicking', () => {
  let isMovedLeft = false;
  let isMovedRight = false;
  const {getByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <Thumbnail
        asset={sideViewAsset}
        context={{
          locale: 'en_US',
          channel: 'ecommerce',
        }}
        readonly={false}
        assetCollection={assets}
        onRemove={() => {}}
        onMove={direction => {
          if (direction === 0) isMovedLeft = true;
          if (direction === 1) isMovedRight = true;
        }}
      />
    </ThemeProvider>
  );

  fireEvent.click(getByText('Right'));
  expect(isMovedLeft).toEqual(false);
  expect(isMovedRight).toEqual(true);
});

test('It trigger event on click by clicking', () => {
  let clicked = false;

  const {getByTestId} = render(
    <ThemeProvider theme={akeneoTheme}>
      <Thumbnail
        asset={sideViewAsset}
        context={{
          locale: 'en_US',
          channel: 'ecommerce',
        }}
        readonly={false}
        assetCollection={assets}
        onRemove={() => {}}
        onMove={() => {}}
        onClick={() => {
          clicked = true;
        }}
      />
    </ThemeProvider>
  );

  fireEvent.click(getByTestId('overlay'));
  expect(clicked).toEqual(true);
});
