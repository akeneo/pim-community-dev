import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {fireEvent, screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
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

test('It renders a thumbnail', () => {
  renderWithProviders(
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
  );
  expect(screen.getByText('pim_asset_manager.asset_collection.remove_asset')).toBeInTheDocument();
  expect(screen.getByTitle('pim_asset_manager.asset_collection.move_asset_to_left')).toBeInTheDocument();
  expect(screen.getByTitle('pim_asset_manager.asset_collection.move_asset_to_right')).toBeInTheDocument();
});
test('It renders the first thumbnail of a collection', () => {
  renderWithProviders(
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
  );
  expect(screen.getByText('pim_asset_manager.asset_collection.remove_asset')).toBeInTheDocument();
  expect(screen.queryByTitle('pim_asset_manager.asset_collection.move_asset_to_left')).not.toBeInTheDocument();
  expect(screen.getByTitle('pim_asset_manager.asset_collection.move_asset_to_right')).toBeInTheDocument();
});
test('It renders the last thumbnail of a collection', () => {
  renderWithProviders(
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
  );
  expect(screen.getByText('pim_asset_manager.asset_collection.remove_asset')).toBeInTheDocument();
  expect(screen.queryByTitle('pim_asset_manager.asset_collection.move_asset_to_right')).not.toBeInTheDocument();
  expect(screen.getByTitle('pim_asset_manager.asset_collection.move_asset_to_left')).toBeInTheDocument();
});

test('It renders a readonly thumbnail', () => {
  renderWithProviders(
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
  );
  expect(screen.queryByText('pim_asset_manager.asset_collection.remove_asset')).not.toBeInTheDocument();
  expect(screen.queryByTitle('pim_asset_manager.asset_collection.move_asset_to_left')).not.toBeInTheDocument();
  expect(screen.queryByTitle('pim_asset_manager.asset_collection.move_asset_to_right')).not.toBeInTheDocument();
});

test('It triggers event on remove asset by clicking', () => {
  let isRemoved = false;
  renderWithProviders(
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
  );

  fireEvent.click(screen.getByText('pim_asset_manager.asset_collection.remove_asset'));
  expect(isRemoved).toEqual(true);
});

test('It triggers event on move asset left by clicking', () => {
  let isMovedLeft = false;
  let isMovedRight = false;
  renderWithProviders(
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
  );

  fireEvent.click(screen.getByTitle('pim_asset_manager.asset_collection.move_asset_to_left'));
  expect(isMovedLeft).toEqual(true);
  expect(isMovedRight).toEqual(false);
});

test('It triggers event on move asset right by clicking', () => {
  let isMovedLeft = false;
  let isMovedRight = false;
  renderWithProviders(
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
  );

  fireEvent.click(screen.getByTitle('pim_asset_manager.asset_collection.move_asset_to_right'));
  expect(isMovedLeft).toEqual(false);
  expect(isMovedRight).toEqual(true);
});

test('It triggers event on click by clicking', () => {
  let clicked = false;

  renderWithProviders(
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
  );

  fireEvent.click(screen.getByTestId('overlay'));
  expect(clicked).toEqual(true);
});
