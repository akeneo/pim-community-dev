import React from 'react';
import {screen, fireEvent, act} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {LinkedProducts} from './linked-products';
import * as hook from './linked-products/useLinkedProducts';

jest.mock('akeneoassetmanager/application/component/asset/edit/linked-products/useProductAttributes', () => ({
  useProductAttributes: () => [
    [
      {
        code: 'AssetCollection',
        type: 'pim_catalog_asset_collection',
        group: 'marketing',
        useable_as_grid_filter: true,
        reference_data_name: 'atmosphere',
        sort_order: 0,
        localizable: false,
        scopable: false,
        labels: {en_US: 'Asset collection'},
        is_locale_specific: false,
        meta: {id: 79},
      },
      {
        code: 'AnotherOne',
        type: 'pim_catalog_asset_collection',
        group: 'marketing',
        useable_as_grid_filter: true,
        reference_data_name: 'atmosphere',
        sort_order: 0,
        localizable: false,
        scopable: false,
        labels: {en_US: 'Another one'},
        is_locale_specific: false,
        meta: {id: 79},
      },
    ],
    {
      code: 'AssetCollection',
      type: 'pim_catalog_asset_collection',
      group: 'marketing',
      useable_as_grid_filter: true,
      reference_data_name: 'atmosphere',
      sort_order: 0,
      localizable: false,
      scopable: false,
      labels: {en_US: 'Asset collection'},
      is_locale_specific: false,
      meta: {id: 79},
    },
    attribute => {
      expect(attribute).toEqual({
        code: 'AnotherOne',
        type: 'pim_catalog_asset_collection',
        group: 'marketing',
        useable_as_grid_filter: true,
        reference_data_name: 'atmosphere',
        sort_order: 0,
        localizable: false,
        scopable: false,
        labels: {en_US: 'Another one'},
        is_locale_specific: false,
        meta: {id: 79},
      });
    },
  ],
}));

jest.mock('akeneoassetmanager/application/component/asset/edit/linked-products/useLinkedProducts');

beforeEach(() => {
  jest.clearAllMocks();
  jest.restoreAllMocks();
});

test('It displays linked products', async () => {
  jest.spyOn(hook, 'useLinkedProducts').mockImplementation(() => [
    [
      {
        id: '12',
        identifier: 'product-1',
        type: 'product',
        labels: {en_US: 'Product 1'},
        image: {
          filePath: 'product-1.jpg',
        },
        completeness: {
          ratio: 100,
        },
      },
      {
        id: '13',
        identifier: 'product-2',
        type: 'product',
        labels: {en_US: 'Product 2'},
        image: {
          filePath: 'rest/asset_manager/image_preview/product-2.jpg',
        },
        completeness: {
          ratio: 80,
        },
      },
    ],
    1,
  ]);

  await renderWithProviders(<LinkedProducts assetFamilyIdentifier="atmosphere" assetCode="atmosphere1" />);

  expect(screen.getByText('pim_asset_manager.asset.enrich.product_subsection')).toBeInTheDocument();
  expect(screen.getByText('Asset collection')).toBeInTheDocument();
  expect(screen.getByText('Product 1')).toBeInTheDocument();
  expect(screen.getByText('100 %')).toBeInTheDocument();
});

test('I can switch selected attribute', async () => {
  jest.spyOn(hook, 'useLinkedProducts').mockImplementation(() => [
    [
      {
        id: '12',
        identifier: 'product-1',
        type: 'product',
        labels: {en_US: 'Product 1'},
        image: null,
        completeness: {
          ratio: 100,
        },
      },
    ],
    10,
  ]);

  await renderWithProviders(<LinkedProducts assetFamilyIdentifier="atmosphere" assetCode="atmosphere1" />);

  fireEvent.click(screen.getByText('Asset collection'));
  fireEvent.click(screen.getByText('Another one'));
  expect(screen.getByText('Product 1')).toBeInTheDocument();
  fireEvent.click(screen.getByText('pim_asset_manager.asset.product.not_enough_items.button'));
});

test('It displays a placeholder if there is no products', async () => {
  jest.spyOn(hook, 'useLinkedProducts').mockImplementation(() => [[], 0]);

  await renderWithProviders(<LinkedProducts assetFamilyIdentifier="another" assetCode="atmosphere1" />);

  expect(screen.getByText('pim_asset_manager.asset.no_linked_products')).toBeInTheDocument();
});

test('It displays a placeholder if there is no products yet', async () => {
  jest.spyOn(hook, 'useLinkedProducts').mockImplementation(() => [null, 0]);

  await renderWithProviders(<LinkedProducts assetFamilyIdentifier="another" assetCode="atmosphere1" />);

  expect(screen.getByText('pim_asset_manager.asset.enrich.product_subsection')).toBeInTheDocument();
});
