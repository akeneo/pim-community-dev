'use strict';

import * as React from 'react';
import * as ReactDOM from 'react-dom';
import '@testing-library/jest-dom/extend-expect';
import {act, fireEvent, getByLabelText, getByText} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import LineList from 'akeneoassetmanager/application/asset-upload/component/line-list';
import {createFakeAssetFamily, createFakeLine} from '../tools';

describe('Test line-list component', () => {
  let container: HTMLElement;

  beforeEach(() => {
    container = document.createElement('div');
    document.body.appendChild(container);
  });

  afterEach(() => {
    document.body.removeChild(container);
  });

  test('It renders without errors', async () => {
    const valuePerLocale = false;
    const valuePerChannel = false;

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <LineList
            lines={[]}
            onLineRemove={() => {}}
            onLineRemoveAll={() => {}}
            onLineChange={() => {}}
            valuePerLocale={valuePerLocale}
            valuePerChannel={valuePerChannel}
          />
        </ThemeProvider>,
        container
      );
    });
  });

  test('It renders the placeholder when empty', async () => {
    const valuePerLocale = false;
    const valuePerChannel = false;

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <LineList
            lines={[]}
            onLineRemove={() => {}}
            onLineRemoveAll={() => {}}
            onLineChange={() => {}}
            valuePerLocale={valuePerLocale}
            valuePerChannel={valuePerChannel}
          />
        </ThemeProvider>,
        container
      );
    });

    const placeholder = getByText(container, 'pim_asset_manager.asset.upload.will_appear_here');
    expect(placeholder).not.toBeNull();
  });

  test('It renders the lines', async () => {
    const valuePerLocale = false;
    const valuePerChannel = false;
    const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);
    const lines = [
      createFakeLine('a.png', assetFamily),
      createFakeLine('b.png', assetFamily),
      createFakeLine('c.png', assetFamily),
    ];

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <LineList
            lines={lines}
            onLineRemove={() => {}}
            onLineRemoveAll={() => {}}
            onLineChange={() => {}}
            valuePerLocale={valuePerLocale}
            valuePerChannel={valuePerChannel}
          />
        </ThemeProvider>,
        container
      );
    });

    const list = getByLabelText(container, 'pim_asset_manager.asset.upload.lines');
    expect(list.children.length).toEqual(3);
  });

  test('It allows me to remove all the lines', async () => {
    const valuePerLocale = false;
    const valuePerChannel = false;
    const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);
    const lines = [
      createFakeLine('a.png', assetFamily),
      createFakeLine('b.png', assetFamily),
      createFakeLine('c.png', assetFamily),
    ];
    const removeAll = jest.fn();

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <LineList
            lines={lines}
            onLineRemove={() => {}}
            onLineRemoveAll={removeAll}
            onLineChange={() => {}}
            valuePerLocale={valuePerLocale}
            valuePerChannel={valuePerChannel}
          />
        </ThemeProvider>,
        container
      );
    });

    const button = getByText(container, 'pim_asset_manager.asset.upload.remove_all');
    fireEvent.click(button);
    expect(removeAll).toHaveBeenCalled();
  });

  test('It renders the list with a locale column', async () => {
    const valuePerLocale = true;
    const valuePerChannel = false;
    const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);
    const lines = [
      createFakeLine('a.png', assetFamily),
      createFakeLine('b.png', assetFamily),
      createFakeLine('c.png', assetFamily),
    ];

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <LineList
            lines={lines}
            onLineRemove={() => {}}
            onLineRemoveAll={() => {}}
            onLineChange={() => {}}
            valuePerLocale={valuePerLocale}
            valuePerChannel={valuePerChannel}
          />
        </ThemeProvider>,
        container
      );
    });

    const header = getByText(container, 'pim_asset_manager.asset.upload.list.locale');
    expect(header).not.toBeNull();
  });

  test('It renders the list with a channel column', async () => {
    const valuePerLocale = false;
    const valuePerChannel = true;
    const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);
    const lines = [
      createFakeLine('a.png', assetFamily),
      createFakeLine('b.png', assetFamily),
      createFakeLine('c.png', assetFamily),
    ];

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <LineList
            lines={lines}
            onLineRemove={() => {}}
            onLineRemoveAll={() => {}}
            onLineChange={() => {}}
            valuePerLocale={valuePerLocale}
            valuePerChannel={valuePerChannel}
          />
        </ThemeProvider>,
        container
      );
    });

    const header = getByText(container, 'pim_asset_manager.asset.upload.list.channel');
    expect(header).not.toBeNull();
  });
});
