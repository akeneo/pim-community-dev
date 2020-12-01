import * as React from 'react';
import * as ReactDOM from 'react-dom';
import '@testing-library/jest-dom/extend-expect';
import {getByText, fireEvent, act} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
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

describe('Test Asset create modal component', () => {
  let container: HTMLElement;

  beforeEach(() => {
    container = document.createElement('div');
    document.body.appendChild(container);
    loadImage.mockImplementation(
      url =>
        new Promise(resolve => {
          act(() => resolve());
        })
    );
  });

  afterEach(() => {
    document.body.removeChild(container);
  });

  test('It displays an unselected asset card', async () => {
    const isSelected = false;
    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <AssetCard
            asset={asset}
            context={{locale: 'en_US', channel: 'ecommerce'}}
            isSelected={isSelected}
            onSelectionChange={() => {}}
          />
        </ThemeProvider>,
        container
      );
    });

    await flushPromises();

    expect(container.querySelector('img')?.src).toEqual('http://localhost/akeneo_asset_manager_image_preview');
    expect(container.querySelector('[data-checked="false"]')).toBeInTheDocument();
    expect(getByText(container, asset.labels.en_US)).toBeInTheDocument();
  });

  test('It displays selected asset card', async () => {
    const isSelected = true;
    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <AssetCard
            asset={asset}
            context={{locale: 'en_US', channel: 'ecommerce'}}
            isSelected={isSelected}
            onSelectionChange={() => {}}
          />
        </ThemeProvider>,
        container
      );
    });

    await flushPromises();

    expect(container.querySelector('img')?.src).toEqual('http://localhost/akeneo_asset_manager_image_preview');
    expect(container.querySelector('[data-checked="true"]')).toBeInTheDocument();
    expect(getByText(container, asset.labels.en_US)).toBeInTheDocument();
  });

  test('it can be selected when clicking on the checkbox', async () => {
    let isSelected = false;
    let selectedCode = null;
    await act(async () => {
      ReactDOM.render(
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
        </ThemeProvider>,
        container
      );
    });

    act(() => {
      fireEvent.click(container.querySelector('[data-checked]'));
    });

    expect(isSelected).toEqual(true);
    expect(selectedCode).toEqual(asset.code);
  });

  test('it can be selected when clicking on the asset card', async () => {
    let isSelected = false;
    let selectedCode = null;
    await act(async () => {
      ReactDOM.render(
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
        </ThemeProvider>,
        container
      );
    });

    act(() => {
      fireEvent.click(container.querySelector('[data-test-id="asset-card-image"]'));
    });

    expect(isSelected).toEqual(true);
    expect(selectedCode).toEqual(asset.code);
  });

  test('it cannot be selected if there is an on change property', async () => {
    let isSelected = false;
    let selectedCode = null;
    let assetCode = '';
    const onClick = jest.fn().mockImplementation((code: string) => {
      assetCode = code;
    });
    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
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
        </ThemeProvider>,
        container
      );
    });

    act(() => {
      fireEvent.click(container.querySelector('[data-test-id="asset-card-image"]'));
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
    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <AssetCard
            asset={asset}
            context={{locale: 'en_US', channel: 'ecommerce'}}
            isSelected={isSelected}
            onSelectionChange={() => {}}
          />
        </ThemeProvider>,
        container
      );
    });

    await flushPromises();

    expect(container.querySelector('img')?.src).toEqual(
      'http://localhost/akeneo_asset_manager_image_preview?type=thumbnail&attributeIdentifier=UNKNOWN&data='
    );
    expect(container.querySelector('[data-checked="true"]')).toBeInTheDocument();
    expect(getByText(container, asset.labels.en_US)).toBeInTheDocument();
  });
});
