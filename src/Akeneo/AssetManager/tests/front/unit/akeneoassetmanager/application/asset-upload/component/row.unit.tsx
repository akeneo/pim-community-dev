'use strict';

import * as React from 'react';
import * as ReactDOM from 'react-dom';
import '@testing-library/jest-dom/extend-expect';
import {act, fireEvent, getByLabelText} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import Row from 'akeneoassetmanager/application/asset-upload/component/row';
import {createFakeAssetFamily, createFakeLine} from '../tools';

describe('Test row component', () => {
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
    const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);
    const line = createFakeLine('foo.png', assetFamily);

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <table>
            <tbody>
              <Row
                line={line}
                onLineRemove={() => {}}
                onLineChange={() => {}}
                valuePerLocale={valuePerLocale}
                valuePerChannel={valuePerChannel}
              />
            </tbody>
          </table>
        </ThemeProvider>,
        container
      );
    });
  });

  test('It renders a row with the code editable', async () => {
    const valuePerLocale = false;
    const valuePerChannel = false;
    const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);
    const line = createFakeLine('foo.png', assetFamily);
    const onLineChange = jest.fn();

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <table>
            <tbody>
              <Row
                line={line}
                onLineRemove={() => {}}
                onLineChange={onLineChange}
                valuePerLocale={valuePerLocale}
                valuePerChannel={valuePerChannel}
              />
            </tbody>
          </table>
        </ThemeProvider>,
        container
      );
    });

    const input = getByLabelText(container, 'pim_asset_manager.asset.upload.list.code') as HTMLInputElement;
    expect(input.value).toEqual('foo');

    fireEvent.change(input, {target: {value: 'foobar'}});
    expect(onLineChange).toHaveBeenCalledWith({
      ...line,
      code: 'foobar',
    });
  });

  test('It renders a row with the code non-editable during asset creation', async () => {
    const valuePerLocale = false;
    const valuePerChannel = false;
    const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);
    const line = {
      ...createFakeLine('foo.png', assetFamily),
      isAssetCreating: true,
    };
    const onLineChange = jest.fn();

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <table>
            <tbody>
              <Row
                line={line}
                onLineRemove={() => {}}
                onLineChange={onLineChange}
                valuePerLocale={valuePerLocale}
                valuePerChannel={valuePerChannel}
              />
            </tbody>
          </table>
        </ThemeProvider>,
        container
      );
    });

    const input = getByLabelText(container, 'pim_asset_manager.asset.upload.list.code') as HTMLInputElement;
    expect(input.readOnly).toEqual(true);
  });

  test('It renders a row with the locale editable', async () => {
    const valuePerLocale = true;
    const valuePerChannel = false;
    const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);
    const line = createFakeLine('foo-en_US.png', assetFamily);
    const onLineChange = jest.fn();

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <table>
            <tbody>
              <Row
                line={line}
                onLineRemove={() => {}}
                onLineChange={onLineChange}
                valuePerLocale={valuePerLocale}
                valuePerChannel={valuePerChannel}
              />
            </tbody>
          </table>
        </ThemeProvider>,
        container
      );
    });

    const input = getByLabelText(container, 'pim_asset_manager.asset.upload.list.locale') as HTMLInputElement;
    expect(input.value).toEqual('en_US');

    fireEvent.change(input, {target: {value: 'fr_FR'}});
    expect(onLineChange).toHaveBeenCalledWith({
      ...line,
      locale: 'fr_FR',
    });
  });

  test('It renders a row with the locale non-editable during asset creation', async () => {
    const valuePerLocale = true;
    const valuePerChannel = false;
    const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);
    const line = {
      ...createFakeLine('foo-en_US.png', assetFamily),
      isAssetCreating: true,
    };
    const onLineChange = jest.fn();

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <table>
            <tbody>
              <Row
                line={line}
                onLineRemove={() => {}}
                onLineChange={onLineChange}
                valuePerLocale={valuePerLocale}
                valuePerChannel={valuePerChannel}
              />
            </tbody>
          </table>
        </ThemeProvider>,
        container
      );
    });

    const input = getByLabelText(container, 'pim_asset_manager.asset.upload.list.locale') as HTMLInputElement;
    expect(input.readOnly).toEqual(true);
  });

  test('It renders a row with the channel editable', async () => {
    const valuePerLocale = false;
    const valuePerChannel = true;
    const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);
    const line = createFakeLine('foo-ecommerce.png', assetFamily);
    const onLineChange = jest.fn();

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <table>
            <tbody>
              <Row
                line={line}
                onLineRemove={() => {}}
                onLineChange={onLineChange}
                valuePerLocale={valuePerLocale}
                valuePerChannel={valuePerChannel}
              />
            </tbody>
          </table>
        </ThemeProvider>,
        container
      );
    });

    const input = getByLabelText(container, 'pim_asset_manager.asset.upload.list.channel') as HTMLInputElement;
    expect(input.value).toEqual('ecommerce');

    fireEvent.change(input, {target: {value: 'mobile'}});
    expect(onLineChange).toHaveBeenCalledWith({
      ...line,
      channel: 'mobile',
    });
  });

  test('It renders a row with the channel non-editable during asset creation', async () => {
    const valuePerLocale = false;
    const valuePerChannel = true;
    const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);
    const line = {
      ...createFakeLine('foo-ecommerce.png', assetFamily),
      isAssetCreating: true,
    };
    const onLineChange = jest.fn();

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <table>
            <tbody>
              <Row
                line={line}
                onLineRemove={() => {}}
                onLineChange={onLineChange}
                valuePerLocale={valuePerLocale}
                valuePerChannel={valuePerChannel}
              />
            </tbody>
          </table>
        </ThemeProvider>,
        container
      );
    });

    const input = getByLabelText(container, 'pim_asset_manager.asset.upload.list.channel') as HTMLInputElement;
    expect(input.readOnly).toEqual(true);
  });

  test('It renders a row and I can remove it', async () => {
    const valuePerLocale = false;
    const valuePerChannel = false;
    const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);
    const line = createFakeLine('foo.png', assetFamily);
    const onLineRemove = jest.fn();

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <table>
            <tbody>
              <Row
                line={line}
                onLineRemove={onLineRemove}
                onLineChange={() => {}}
                valuePerLocale={valuePerLocale}
                valuePerChannel={valuePerChannel}
              />
            </tbody>
          </table>
        </ThemeProvider>,
        container
      );
    });

    const button = getByLabelText(container, 'pim_asset_manager.asset.upload.remove');

    fireEvent.click(button);
    expect(onLineRemove).toHaveBeenCalledWith(line);
  });
});
