import React from 'react';
import {screen, fireEvent, act} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import AssetCard from 'akeneoassetmanager/application/component/asset/list/mosaic/asset-card';
import loadImage from 'akeneoassetmanager/tools/image-loader';

const flushPromises = () => new Promise(setImmediate);

const asset = {
  code: 'iphone',
  labels: {
    en_US: 'iPhone X',
  },
  image: [{attribute: 'nice', locale: null, channel: null, data: {filePath: 'my_image_url', originalFilename: ''}}],
  completeness: {},
};
jest.mock('akeneoassetmanager/tools/image-loader');

jest.mock('@akeneo-pim-community/shared/lib/hooks/useRouter', () => ({
  useRouter: () => {
    return {
      redirect: jest.fn(),
      generate: jest.fn(
        (route: string, parameters: URLSearchParams) => route + '?' + new URLSearchParams(parameters).toString()
      ),
    };
  },
}));

type EntryCallback = (entries: {isIntersecting: boolean}[]) => void;
beforeEach(() => {
  const immediateIntersectionObserver = (callback: EntryCallback) => ({
    observe: jest.fn(() => callback([{isIntersecting: true}])),
    unobserve: jest.fn(),
  });

  window.IntersectionObserver = jest.fn().mockImplementation(immediateIntersectionObserver);
});

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
        containerRef={{current: null}}
      />
    );

    await act(async () => {
      await flushPromises();
    });

    const image = screen.getByRole('img') as HTMLImageElement;

    expect(image.src).toEqual(
      'http://localhost/akeneo_asset_manager_image_preview?type=thumbnail&attributeIdentifier=nice&data=bXlfaW1hZ2VfdXJs'
    );
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
        containerRef={{current: null}}
      />
    );

    await act(async () => {
      await flushPromises();
    });

    const image = screen.getByRole('img') as HTMLImageElement;

    expect(image.src).toEqual(
      'http://localhost/akeneo_asset_manager_image_preview?type=thumbnail&attributeIdentifier=nice&data=bXlfaW1hZ2VfdXJs'
    );
    expect(screen.getByRole('checkbox')).toBeInTheDocument();
    expect(screen.getByRole('checkbox')).toHaveAttribute('aria-checked', 'true');
    expect(screen.getByText(asset.labels.en_US)).toBeInTheDocument();
  });

  test('it can be selected when clicking on the checkbox', async () => {
    const handleSelectionChange = jest.fn();

    renderWithProviders(
      <AssetCard
        asset={asset}
        context={{locale: 'en_US', channel: 'ecommerce'}}
        isSelected={false}
        onSelectionChange={handleSelectionChange}
        containerRef={{current: null}}
      />
    );

    await act(async () => {
      await flushPromises();
    });

    fireEvent.click(screen.getByRole('checkbox'));

    expect(handleSelectionChange).toHaveBeenCalledWith(asset.code, true);
  });

  test('it can be selected when clicking on the asset card', async () => {
    const handleSelectionChange = jest.fn();
    renderWithProviders(
      <AssetCard
        asset={asset}
        context={{locale: 'en_US', channel: 'ecommerce'}}
        isSelected={false}
        onSelectionChange={handleSelectionChange}
        containerRef={{current: null}}
      />
    );

    await act(async () => {
      await flushPromises();
      fireEvent.click(screen.getByRole('img'));
    });

    expect(handleSelectionChange).toHaveBeenCalledWith(asset.code, true);
  });

  test('it calls onClick when clicking on the image', async () => {
    const handleSelectionChange = jest.fn();
    const handleClick = jest.fn();

    renderWithProviders(
      <AssetCard
        asset={asset}
        context={{locale: 'en_US', channel: 'ecommerce'}}
        isSelected={false}
        onClick={handleClick}
        onSelectionChange={handleSelectionChange}
        containerRef={{current: null}}
      />
    );

    await act(async () => {
      await flushPromises();
      fireEvent.click(screen.getByRole('img'));
    });

    expect(handleSelectionChange).not.toBeCalled();
    expect(handleClick).toBeCalledWith(asset.code);
  });

  test('It displays nothing if the asset fetch failed', async () => {
    loadImage.mockImplementation(
      () =>
        new Promise((_resolve, reject) => {
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
        containerRef={{current: null}}
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
