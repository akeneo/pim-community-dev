import React from 'react';
import {screen, fireEvent} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import * as utils from 'akeneoassetmanager/application/asset-upload/utils/utils';
import Row from 'akeneoassetmanager/application/asset-upload/component/row';
import {createFakeAssetFamily, createFakeChannel, createFakeError, createFakeLine, createFakeLocale} from '../tools';
import Line, {LineStatus} from 'akeneoassetmanager/application/asset-upload/model/line';
import Channel from 'akeneoassetmanager/domain/model/channel';
import Locale from 'akeneoassetmanager/domain/model/locale';

jest.mock('akeneoassetmanager/application/component/app/select2');

describe('Test row component', () => {
  test('It renders without errors', () => {
    const valuePerLocale = false;
    const valuePerChannel = false;
    const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);
    const channels: Channel[] = [];
    const locales: Locale[] = [];
    const line = createFakeLine('foo.png', assetFamily, channels, locales);

    renderWithProviders(
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
    );
  });

  test('It renders a row with the code editable', () => {
    const valuePerLocale = false;
    const valuePerChannel = false;
    const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);
    const channels: Channel[] = [];
    const locales: Locale[] = [];
    const line = createFakeLine('foo.png', assetFamily, channels, locales);
    const onLineChange = jest.fn();

    renderWithProviders(
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
    );

    const input = screen.getByLabelText('pim_asset_manager.asset.upload.list.code') as HTMLInputElement;
    expect(input.value).toEqual('foo');

    fireEvent.change(input, {target: {value: 'foobar'}});
    expect(onLineChange).toHaveBeenCalledWith({
      ...line,
      code: 'foobar',
    });
  });

  test('It renders a row with the code non-editable during asset creation', () => {
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

    renderWithProviders(
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
    );

    const input = screen.getByLabelText('pim_asset_manager.asset.upload.list.code') as HTMLInputElement;
    expect(input.disabled).toEqual(true);
  });

  test('It renders a row with the locale editable', () => {
    const valuePerLocale = true;
    const valuePerChannel = false;
    const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);
    const channels: Channel[] = [];
    const locales: Locale[] = [createFakeLocale('en_US'), createFakeLocale('fr_FR')];
    const line = createFakeLine('foo-en_US.png', assetFamily, channels, locales);
    const onLineChange = jest.fn();

    renderWithProviders(
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
    );

    const input = screen.getByLabelText('pim_asset_manager.asset.upload.list.locale') as HTMLSelectElement;
    expect(input.value).toEqual('en_US');

    fireEvent.change(input, {target: {value: 'fr_FR'}});
    expect(onLineChange).toHaveBeenCalledWith({
      ...line,
      locale: 'fr_FR',
    });
  });

  test('It renders a row with the locale selectable from a list with flags', () => {
    const valuePerLocale = true;
    const valuePerChannel = false;
    const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);
    const channels: Channel[] = [];
    const locales: Locale[] = [createFakeLocale('en_US'), createFakeLocale('fr_FR')];
    const line = createFakeLine('foo-en_US.png', assetFamily, channels, locales);
    const onLineChange = jest.fn();

    renderWithProviders(
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
    );

    const input = screen.getByLabelText('pim_asset_manager.asset.upload.list.locale') as HTMLSelectElement;
  });

  test('It renders a row with the locale non-editable during asset creation', () => {
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

    renderWithProviders(
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
    );

    const input = screen.getByLabelText('pim_asset_manager.asset.upload.list.locale') as HTMLSelectElement;
    expect(input.disabled).toEqual(true);
  });

  test('It renders a row with the channel editable', () => {
    const valuePerLocale = false;
    const valuePerChannel = true;
    const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);
    const channels: Channel[] = [createFakeChannel('ecommerce', ['en_US']), createFakeChannel('mobile', ['en_US'])];
    const locales: Locale[] = [];
    const line = createFakeLine('foo-ecommerce.png', assetFamily, channels, locales);
    const onLineChange = jest.fn();

    renderWithProviders(
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
    );

    const input = screen.getByLabelText('pim_asset_manager.asset.upload.list.channel') as HTMLSelectElement;
    expect(input.value).toEqual('ecommerce');

    fireEvent.change(input, {target: {value: 'mobile'}});
    expect(onLineChange).toHaveBeenCalledWith({
      ...line,
      channel: 'mobile',
    });
  });

  test('It renders a row with the channel non-editable during asset creation', () => {
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

    renderWithProviders(
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
    );

    const input = screen.getByLabelText('pim_asset_manager.asset.upload.list.channel') as HTMLSelectElement;
    expect(input.disabled).toEqual(true);
  });

  test('It renders a row and I can remove it', () => {
    const valuePerLocale = false;
    const valuePerChannel = false;
    const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);
    const channels: Channel[] = [];
    const locales: Locale[] = [];
    const line = createFakeLine('foo.png', assetFamily, channels, locales);
    const onLineRemove = jest.fn();

    renderWithProviders(
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
    );

    const button = screen.getByTitle('pim_asset_manager.asset.upload.remove');

    fireEvent.click(button);
    expect(onLineRemove).toHaveBeenCalledWith(line);
  });

  test('It renders a row with a global error', () => {
    jest.spyOn(utils, 'getStatusFromLine').mockImplementation((_line: Line) => LineStatus.Invalid);
    jest.spyOn(utils, 'getAllErrorsOfLineByTarget').mockImplementation((_line: Line) => ({
      common: [createFakeError('Some error')],
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

    renderWithProviders(
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
    );

    const error = screen.getByLabelText('Some error');
    expect(error).not.toBeNull();
  });

  test('It renders a row with an error on code', () => {
    jest.spyOn(utils, 'getStatusFromLine').mockImplementation((_line: Line) => LineStatus.Invalid);
    jest.spyOn(utils, 'getAllErrorsOfLineByTarget').mockImplementation((_line: Line) => ({
      common: [],
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

    renderWithProviders(
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
    );

    const error = screen.getByLabelText('Some error');
    expect(error).not.toBeNull();
  });

  test('It renders a row with an error on channel', () => {
    jest.spyOn(utils, 'getStatusFromLine').mockImplementation((_line: Line) => LineStatus.Invalid);
    jest.spyOn(utils, 'getAllErrorsOfLineByTarget').mockImplementation((_line: Line) => ({
      common: [],
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

    renderWithProviders(
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
    );

    const error = screen.getByLabelText('Some error');
    expect(error).not.toBeNull();
  });

  test('It renders a row with an error on locale', () => {
    jest.spyOn(utils, 'getStatusFromLine').mockImplementation((_line: Line) => LineStatus.Invalid);
    jest.spyOn(utils, 'getAllErrorsOfLineByTarget').mockImplementation((_line: Line) => ({
      common: [],
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

    renderWithProviders(
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
    );

    const error = screen.getByLabelText('Some error');
    expect(error).not.toBeNull();
  });
});
