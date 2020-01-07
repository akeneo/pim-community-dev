'use strict';

import * as React from 'react';
import * as ReactDOM from 'react-dom';
import '@testing-library/jest-dom/extend-expect';
import {act, fireEvent, getByLabelText} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import * as utils from 'akeneoassetmanager/application/asset-upload/utils/utils';
import Row from 'akeneoassetmanager/application/asset-upload/component/row';
import {createFakeAssetFamily, createFakeChannel, createFakeError, createFakeLine, createFakeLocale} from '../tools';
import Line, {LineStatus} from 'akeneoassetmanager/application/asset-upload/model/line';
import Channel from 'akeneoassetmanager/domain/model/channel';
import Locale from 'akeneoassetmanager/domain/model/locale';

jest.mock('akeneoassetmanager/application/component/app/select2');

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
    const channels: Channel[] = [];
    const locales: Locale[] = [];
    const line = createFakeLine('foo.png', assetFamily, channels, locales);

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <Row
            line={line}
            locale="en_US"
            channels={channels}
            locales={locales}
            onLineRemove={() => {}}
            onLineChange={() => {}}
            valuePerLocale={valuePerLocale}
            valuePerChannel={valuePerChannel}
          />
        </ThemeProvider>,
        container
      );
    });
  });

  test('It renders a row with the code editable', async () => {
    const valuePerLocale = false;
    const valuePerChannel = false;
    const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);
    const channels: Channel[] = [];
    const locales: Locale[] = [];
    const line = createFakeLine('foo.png', assetFamily, channels, locales);
    const onLineChange = jest.fn();

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <Row
            line={line}
            locale="en_US"
            channels={channels}
            locales={locales}
            onLineRemove={() => {}}
            onLineChange={onLineChange}
            valuePerLocale={valuePerLocale}
            valuePerChannel={valuePerChannel}
          />
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
    const channels: Channel[] = [];
    const locales: Locale[] = [];
    const line = {
      ...createFakeLine('foo.png', assetFamily, channels, locales),
      isAssetCreating: true,
    };
    const onLineChange = jest.fn();

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <Row
            line={line}
            locale="en_US"
            channels={channels}
            locales={locales}
            onLineRemove={() => {}}
            onLineChange={onLineChange}
            valuePerLocale={valuePerLocale}
            valuePerChannel={valuePerChannel}
          />
        </ThemeProvider>,
        container
      );
    });

    const input = getByLabelText(container, 'pim_asset_manager.asset.upload.list.code') as HTMLInputElement;
    expect(input.disabled).toEqual(true);
  });

  test('It renders a row with the locale editable', async () => {
    const valuePerLocale = true;
    const valuePerChannel = false;
    const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);
    const channels: Channel[] = [];
    const locales: Locale[] = [createFakeLocale('en_US'), createFakeLocale('fr_FR')];
    const line = createFakeLine('foo-en_US.png', assetFamily, channels, locales);
    const onLineChange = jest.fn();

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <Row
            line={line}
            locale="en_US"
            channels={channels}
            locales={locales}
            onLineRemove={() => {}}
            onLineChange={onLineChange}
            valuePerLocale={valuePerLocale}
            valuePerChannel={valuePerChannel}
          />
        </ThemeProvider>,
        container
      );
    });

    const input = getByLabelText(container, 'pim_asset_manager.asset.upload.list.locale') as HTMLSelectElement;
    expect(input.value).toEqual('en_US');

    fireEvent.change(input, {target: {value: 'fr_FR'}});
    expect(onLineChange).toHaveBeenCalledWith({
      ...line,
      locale: 'fr_FR',
    });
  });

  test('It renders a row with the locale selectable from a list with flags', async () => {
    const valuePerLocale = true;
    const valuePerChannel = false;
    const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);
    const channels: Channel[] = [];
    const locales: Locale[] = [createFakeLocale('en_US'), createFakeLocale('fr_FR')];
    const line = createFakeLine('foo-en_US.png', assetFamily, channels, locales);
    const onLineChange = jest.fn();

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <Row
            line={line}
            locale="en_US"
            channels={channels}
            locales={locales}
            onLineRemove={() => {}}
            onLineChange={onLineChange}
            valuePerLocale={valuePerLocale}
            valuePerChannel={valuePerChannel}
          />
        </ThemeProvider>,
        container
      );
    });

    const input = getByLabelText(container, 'pim_asset_manager.asset.upload.list.locale') as HTMLSelectElement;
  });

  test('It renders a row with the locale non-editable during asset creation', async () => {
    const valuePerLocale = true;
    const valuePerChannel = false;
    const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);
    const channels: Channel[] = [];
    const locales: Locale[] = [];
    const line = {
      ...createFakeLine('foo-en_US.png', assetFamily, channels, locales),
      isAssetCreating: true,
    };
    const onLineChange = jest.fn();

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <Row
            line={line}
            locale="en_US"
            channels={channels}
            locales={locales}
            onLineRemove={() => {}}
            onLineChange={onLineChange}
            valuePerLocale={valuePerLocale}
            valuePerChannel={valuePerChannel}
          />
        </ThemeProvider>,
        container
      );
    });

    const input = getByLabelText(container, 'pim_asset_manager.asset.upload.list.locale') as HTMLSelectElement;
    expect(input.disabled).toEqual(true);
  });

  test('It renders a row with the channel editable', async () => {
    const valuePerLocale = false;
    const valuePerChannel = true;
    const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);
    const channels: Channel[] = [createFakeChannel('ecommerce', ['en_US']), createFakeChannel('mobile', ['en_US'])];
    const locales: Locale[] = [];
    const line = createFakeLine('foo-ecommerce.png', assetFamily, channels, locales);
    const onLineChange = jest.fn();

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <Row
            line={line}
            locale="en_US"
            channels={channels}
            locales={locales}
            onLineRemove={() => {}}
            onLineChange={onLineChange}
            valuePerLocale={valuePerLocale}
            valuePerChannel={valuePerChannel}
          />
        </ThemeProvider>,
        container
      );
    });

    const input = getByLabelText(container, 'pim_asset_manager.asset.upload.list.channel') as HTMLSelectElement;
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
    const channels: Channel[] = [];
    const locales: Locale[] = [];
    const line = {
      ...createFakeLine('foo-ecommerce.png', assetFamily, channels, locales),
      isAssetCreating: true,
    };
    const onLineChange = jest.fn();

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <Row
            line={line}
            locale="en_US"
            channels={channels}
            locales={locales}
            onLineRemove={() => {}}
            onLineChange={onLineChange}
            valuePerLocale={valuePerLocale}
            valuePerChannel={valuePerChannel}
          />
        </ThemeProvider>,
        container
      );
    });

    const input = getByLabelText(container, 'pim_asset_manager.asset.upload.list.channel') as HTMLSelectElement;
    expect(input.disabled).toEqual(true);
  });

  test('It renders a row and I can remove it', async () => {
    const valuePerLocale = false;
    const valuePerChannel = false;
    const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);
    const channels: Channel[] = [];
    const locales: Locale[] = [];
    const line = createFakeLine('foo.png', assetFamily, channels, locales);
    const onLineRemove = jest.fn();

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <Row
            line={line}
            locale="en_US"
            channels={channels}
            locales={locales}
            onLineRemove={onLineRemove}
            onLineChange={() => {}}
            valuePerLocale={valuePerLocale}
            valuePerChannel={valuePerChannel}
          />
        </ThemeProvider>,
        container
      );
    });

    const button = getByLabelText(container, 'pim_asset_manager.asset.upload.remove');

    fireEvent.click(button);
    expect(onLineRemove).toHaveBeenCalledWith(line);
  });

  test('It renders a row with a global error', async () => {
    jest.spyOn(utils, 'getStatusFromLine').mockImplementation((_line: Line) => LineStatus.Invalid);
    jest.spyOn(utils, 'getAllErrorsOfLineByTarget').mockImplementation((_line: Line) => ({
      all: [createFakeError('Some error')],
      code: [],
      channel: [],
      locale: [],
    }));

    const valuePerLocale = false;
    const valuePerChannel = false;
    const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);
    const channels: Channel[] = [];
    const locales: Locale[] = [];
    const line = createFakeLine('foo.png', assetFamily, channels, locales);

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <Row
            line={line}
            locale="en_US"
            channels={channels}
            locales={locales}
            onLineRemove={() => {}}
            onLineChange={() => {}}
            valuePerLocale={valuePerLocale}
            valuePerChannel={valuePerChannel}
          />
        </ThemeProvider>,
        container
      );
    });

    const error = getByLabelText(container, 'Some error');
    expect(error).not.toBeNull();
  });

  test('It renders a row with an error on code', async () => {
    jest.spyOn(utils, 'getStatusFromLine').mockImplementation((_line: Line) => LineStatus.Invalid);
    jest.spyOn(utils, 'getAllErrorsOfLineByTarget').mockImplementation((_line: Line) => ({
      all: [],
      code: [createFakeError('Some error')],
      channel: [],
      locale: [],
    }));

    const valuePerLocale = false;
    const valuePerChannel = false;
    const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);
    const channels: Channel[] = [];
    const locales: Locale[] = [];
    const line = createFakeLine('foo.png', assetFamily, channels, locales);

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <Row
            line={line}
            locale="en_US"
            channels={channels}
            locales={locales}
            onLineRemove={() => {}}
            onLineChange={() => {}}
            valuePerLocale={valuePerLocale}
            valuePerChannel={valuePerChannel}
          />
        </ThemeProvider>,
        container
      );
    });

    const error = getByLabelText(container, 'Some error');
    expect(error).not.toBeNull();
  });

  test('It renders a row with an error on channel', async () => {
    jest.spyOn(utils, 'getStatusFromLine').mockImplementation((_line: Line) => LineStatus.Invalid);
    jest.spyOn(utils, 'getAllErrorsOfLineByTarget').mockImplementation((_line: Line) => ({
      all: [],
      code: [],
      channel: [createFakeError('Some error')],
      locale: [],
    }));

    const valuePerLocale = false;
    const valuePerChannel = false;
    const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);
    const channels: Channel[] = [];
    const locales: Locale[] = [];
    const line = createFakeLine('foo.png', assetFamily, channels, locales);

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <Row
            line={line}
            locale="en_US"
            channels={channels}
            locales={locales}
            onLineRemove={() => {}}
            onLineChange={() => {}}
            valuePerLocale={valuePerLocale}
            valuePerChannel={valuePerChannel}
          />
        </ThemeProvider>,
        container
      );
    });

    const error = getByLabelText(container, 'Some error');
    expect(error).not.toBeNull();
  });

  test('It renders a row with an error on locale', async () => {
    jest.spyOn(utils, 'getStatusFromLine').mockImplementation((_line: Line) => LineStatus.Invalid);
    jest.spyOn(utils, 'getAllErrorsOfLineByTarget').mockImplementation((_line: Line) => ({
      all: [],
      code: [],
      channel: [],
      locale: [createFakeError('Some error')],
    }));

    const valuePerLocale = false;
    const valuePerChannel = false;
    const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);
    const channels: Channel[] = [];
    const locales: Locale[] = [];
    const line = createFakeLine('foo.png', assetFamily, channels, locales);

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <Row
            line={line}
            locale="en_US"
            channels={channels}
            locales={locales}
            onLineRemove={() => {}}
            onLineChange={() => {}}
            valuePerLocale={valuePerLocale}
            valuePerChannel={valuePerChannel}
          />
        </ThemeProvider>,
        container
      );
    });

    const error = getByLabelText(container, 'Some error');
    expect(error).not.toBeNull();
  });
});
