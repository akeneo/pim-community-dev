import React from 'react';
import {screen, fireEvent, act} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import AssetCard from 'akeneoassetmanager/application/component/asset/list/mosaic/asset-card';
import loadImage from 'akeneoassetmanager/tools/image-loader';

const flushPromises = () => new Promise(setImmediate);
const routing = require('routing');
jest.mock('routing');

const asset = {
  code: 'iphone',
  labels: {
    en_US: 'iPhone X',
  },
  image: [{attribute: 'nice', locale: null, channel: null, data: {filePath: 'my_image_url', originalFilename: ''}}],
  completeness: {},
};
jest.mock('akeneoassetmanager/tools/image-loader');

loadImage.mockImplementation(
  () =>
    new Promise(resolve => {
      act(() => resolve());
    })
);

describe('Test Asset create modal component', () => {
  test('It displays an unselected asset card', async () => {
    renderWithProviders(
      <AssetCard
        asset={asset}
        context={{locale: 'en_US', channel: 'ecommerce'}}
        isSelected={false}
        onSelectionChange={() => {}}
      />
    );

    await act(async () => {
      await flushPromises();
    });

    const image = screen.getByRole('img') as HTMLImageElement;

    expect(image.src).toEqual('http://localhost/akeneo_asset_manager_image_preview');
    expect(screen.getByRole('checkbox')).toBeInTheDocument();
    expect(screen.getByRole('checkbox')).toHaveAttribute('aria-checked', 'false');
    expect(screen.getByText(asset.labels.en_US)).toBeInTheDocument();
  });

  test('It displays selected asset card', async () => {
    const isSelected = true;
    renderWithProviders(
      <AssetCard
        asset={asset}
        context={{locale: 'en_US', channel: 'ecommerce'}}
        isSelected={isSelected}
        onSelectionChange={() => {}}
      />
    );

    await act(async () => {
      await flushPromises();
    });

    const image = screen.getByRole('img') as HTMLImageElement;

    expect(image.src).toEqual('http://localhost/akeneo_asset_manager_image_preview');
    expect(screen.getByRole('checkbox')).toBeInTheDocument();
    expect(screen.getByRole('checkbox')).toHaveAttribute('aria-checked', 'true');
    expect(screen.getByText(asset.labels.en_US)).toBeInTheDocument();
  });

  test('it can be selected when clicking on the checkbox', async () => {
    let isSelected = false;
    let selectedCode = null;
    renderWithProviders(
      <AssetCard
        asset={asset}
        context={{locale: 'en_US', channel: 'ecommerce'}}
        isSelected={isSelected}
        onSelectionChange={(code, value) => {
          isSelected = value;
          selectedCode = code;
        }}
      />
    );

    await act(async () => {
      await flushPromises();
      fireEvent.click(screen.getByRole('checkbox'));
    });

    expect(isSelected).toEqual(true);
    expect(selectedCode).toEqual(asset.code);
  });

  test('it can be selected when clicking on the asset card', async () => {
    let isSelected = false;
    let selectedCode = null;
    renderWithProviders(
      <AssetCard
        asset={asset}
        context={{locale: 'en_US', channel: 'ecommerce'}}
        isSelected={isSelected}
        onSelectionChange={(code, value) => {
          isSelected = value;
          selectedCode = code;
        }}
      />
    );

    await act(async () => {
      await flushPromises();
      fireEvent.click(screen.getByRole('img'));
    });

    expect(isSelected).toEqual(true);
    expect(selectedCode).toEqual(asset.code);
  });

  test('it calls onClick when clicking on the image', async () => {
    let isSelected = false;
    let selectedCode = null;
    let assetCode = '';
    const onClick = jest.fn().mockImplementation((code: string) => {
      assetCode = code;
    });
    renderWithProviders(
      <AssetCard
        asset={asset}
        context={{locale: 'en_US', channel: 'ecommerce'}}
        isSelected={isSelected}
        onClick={onClick}
        onSelectionChange={(code, value) => {
          isSelected = value;
          selectedCode = code;
        }}
      />
    );

    await act(async () => {
      await flushPromises();
      fireEvent.click(screen.getByRole('img'));
    });

    expect(isSelected).toEqual(false);
    expect(selectedCode).toEqual(null);
    expect(onClick).toBeCalled();
    expect(assetCode).toEqual(asset.code);
  });

  test('It displays nothing if the asset fetch failed', async () => {
    routing.generate = jest
      .fn()
      .mockImplementation((route: string, parameters: any) => route + '?' + new URLSearchParams(parameters).toString());

    loadImage.mockImplementation(
      url =>
        new Promise((resolve, reject) => {
          act(() => reject());
        })
    );
    const isSelected = true;
    renderWithProviders(
      <AssetCard
        asset={asset}
        context={{locale: 'en_US', channel: 'ecommerce'}}
        isSelected={isSelected}
        onSelectionChange={() => {}}
      />
    );

    await act(async () => {
      await flushPromises();
    });

    const image = screen.queryByRole('img');

    expect(image).toBeInTheDocument();
    expect(image).toHaveAttribute(
      'src',
      'akeneo_asset_manager_image_preview?type=thumbnail&attributeIdentifier=UNKNOWN&data='
    );
    expect(screen.getByText(asset.labels.en_US)).toBeInTheDocument();
  });
});
