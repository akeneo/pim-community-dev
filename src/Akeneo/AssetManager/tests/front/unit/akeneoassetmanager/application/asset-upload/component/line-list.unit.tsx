'use strict';

import * as React from 'react';
import * as ReactDOM from 'react-dom';
import '@testing-library/jest-dom/extend-expect';
import {act, fireEvent, getByLabelText, getByText} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import Row from 'akeneoassetmanager/application/asset-upload/component/row';
import {createLineFromFilename} from 'akeneoassetmanager/application/asset-upload/utils/utils';
import LineList from 'akeneoassetmanager/application/asset-upload/component/line-list';

describe('Test line-list component', () => {
  let container: HTMLElement;

  const createAssetFamily = (valuePerLocale: boolean, valuePerChannel: boolean) => {
    return Object.freeze({
      identifier: 'packshot',
      code: 'packshot',
      labels: {en_US: 'Packshot'},
      image: null,
      attributeAsLabel: 'name',
      attributeAsMainMedia: 'picture_fingerprint',
      attributes: [
        {
          identifier: 'name',
          asset_family_identifier: 'name',
          code: 'name',
          type: 'text',
          labels: {en_US: 'Name'},
          order: 0,
          is_required: true,
          value_per_locale: false,
          value_per_channel: false,
        },
        {
          identifier: 'picture_fingerprint',
          asset_family_identifier: 'packshot',
          code: 'picture',
          type: 'media_file',
          labels: {en_US: 'Picture'},
          order: 0,
          is_required: true,
          value_per_locale: valuePerLocale,
          value_per_channel: valuePerChannel,
        },
      ],
    });
  };

  const createLine = (filename: string, valuePerLocale: boolean, valuePerChannel: boolean) => {
    return Object.freeze({
      ...createLineFromFilename(filename, createAssetFamily(valuePerLocale, valuePerChannel)),
      thumbnail: '/tmb/' + filename,
    });
  };

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
            onLineRemove={() => null}
            onLineRemoveAll={() => null}
            onLineChange={() => null}
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
            onLineRemove={() => null}
            onLineRemoveAll={() => null}
            onLineChange={() => null}
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
    const lines = [
      createLine('a.png', valuePerLocale, valuePerChannel),
      createLine('b.png', valuePerLocale, valuePerChannel),
      createLine('c.png', valuePerLocale, valuePerChannel),
    ];

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <LineList
            lines={lines}
            onLineRemove={() => null}
            onLineRemoveAll={() => null}
            onLineChange={() => null}
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
    const lines = [
      createLine('a.png', valuePerLocale, valuePerChannel),
      createLine('b.png', valuePerLocale, valuePerChannel),
      createLine('c.png', valuePerLocale, valuePerChannel),
    ];
    const removeAll = jest.fn();

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <LineList
            lines={lines}
            onLineRemove={() => null}
            onLineRemoveAll={removeAll}
            onLineChange={() => null}
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
    const lines = [
      createLine('a.png', valuePerLocale, valuePerChannel),
      createLine('b.png', valuePerLocale, valuePerChannel),
      createLine('c.png', valuePerLocale, valuePerChannel),
    ];

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <LineList
            lines={lines}
            onLineRemove={() => null}
            onLineRemoveAll={() => null}
            onLineChange={() => null}
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
    const lines = [
      createLine('a.png', valuePerLocale, valuePerChannel),
      createLine('b.png', valuePerLocale, valuePerChannel),
      createLine('c.png', valuePerLocale, valuePerChannel),
    ];

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <LineList
            lines={lines}
            onLineRemove={() => null}
            onLineRemoveAll={() => null}
            onLineChange={() => null}
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
