import React from 'react';
import {screen, fireEvent} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import LineList from 'akeneoassetmanager/application/asset-upload/component/line-list';
import {createFakeAssetFamily, createFakeLine} from '../tools';
import Channel from 'akeneoassetmanager/domain/model/channel';
import Locale from 'akeneoassetmanager/domain/model/locale';

jest.mock('akeneoassetmanager/application/component/app/select2');

describe('Test line-list component', () => {
  test('It renders without errors', () => {
    const valuePerLocale = false;
    const valuePerChannel = false;
    const channels: Channel[] = [];
    const locales: Locale[] = [];

    renderWithProviders(
      <LineList
        lines={[]}
        locale="en_US"
        channels={channels}
        locales={locales}
        onLineRemove={() => {}}
        onLineRemoveAll={() => {}}
        onLineChange={() => {}}
        valuePerLocale={valuePerLocale}
        valuePerChannel={valuePerChannel}
      />
    );
  });

  test('It renders the placeholder when empty', () => {
    const valuePerLocale = false;
    const valuePerChannel = false;
    const channels: Channel[] = [];
    const locales: Locale[] = [];

    renderWithProviders(
      <LineList
        lines={[]}
        locale="en_US"
        channels={channels}
        locales={locales}
        onLineRemove={() => {}}
        onLineRemoveAll={() => {}}
        onLineChange={() => {}}
        valuePerLocale={valuePerLocale}
        valuePerChannel={valuePerChannel}
      />
    );

    const placeholder = screen.getByText('pim_asset_manager.asset.upload.will_appear_here');
    expect(placeholder).not.toBeNull();
  });

  test('It renders the lines', () => {
    const valuePerLocale = false;
    const valuePerChannel = false;
    const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);
    const channels: Channel[] = [];
    const locales: Locale[] = [];
    const lines = [
      createFakeLine('a.png', assetFamily, channels, locales),
      createFakeLine('b.png', assetFamily, channels, locales),
      createFakeLine('c.png', assetFamily, channels, locales),
    ];

    renderWithProviders(
      <LineList
        lines={lines}
        locale="en_US"
        channels={channels}
        locales={locales}
        onLineRemove={() => {}}
        onLineRemoveAll={() => {}}
        onLineChange={() => {}}
        valuePerLocale={valuePerLocale}
        valuePerChannel={valuePerChannel}
      />
    );

    const list = screen.getByLabelText('pim_asset_manager.asset.upload.lines');
    expect(list.children.length).toEqual(3);
  });

  test('It allows me to remove all the lines', () => {
    const valuePerLocale = false;
    const valuePerChannel = false;
    const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);
    const channels: Channel[] = [];
    const locales: Locale[] = [];
    const lines = [
      createFakeLine('a.png', assetFamily, channels, locales),
      createFakeLine('b.png', assetFamily, channels, locales),
      createFakeLine('c.png', assetFamily, channels, locales),
    ];
    const removeAll = jest.fn();

    renderWithProviders(
      <LineList
        lines={lines}
        locale="en_US"
        channels={channels}
        locales={locales}
        onLineRemove={() => {}}
        onLineRemoveAll={removeAll}
        onLineChange={() => {}}
        valuePerLocale={valuePerLocale}
        valuePerChannel={valuePerChannel}
      />
    );

    const button = screen.getByText('pim_asset_manager.asset.upload.remove_all');
    fireEvent.click(button);
    expect(removeAll).toHaveBeenCalled();
  });

  test('It renders the list with a locale column', () => {
    const valuePerLocale = true;
    const valuePerChannel = false;
    const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);
    const channels: Channel[] = [];
    const locales: Locale[] = [];
    const lines = [
      createFakeLine('a.png', assetFamily, channels, locales),
      createFakeLine('b.png', assetFamily, channels, locales),
      createFakeLine('c.png', assetFamily, channels, locales),
    ];

    renderWithProviders(
      <LineList
        lines={lines}
        locale="en_US"
        channels={channels}
        locales={locales}
        onLineRemove={() => {}}
        onLineRemoveAll={() => {}}
        onLineChange={() => {}}
        valuePerLocale={valuePerLocale}
        valuePerChannel={valuePerChannel}
      />
    );

    const header = screen.getByText('pim_asset_manager.asset.upload.list.locale');
    expect(header).not.toBeNull();
  });

  test('It renders the list with a channel column', () => {
    const valuePerLocale = false;
    const valuePerChannel = true;
    const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);
    const channels: Channel[] = [];
    const locales: Locale[] = [];
    const lines = [
      createFakeLine('a.png', assetFamily, channels, locales),
      createFakeLine('b.png', assetFamily, channels, locales),
      createFakeLine('c.png', assetFamily, channels, locales),
    ];

    renderWithProviders(
      <LineList
        lines={lines}
        locale="en_US"
        channels={channels}
        locales={locales}
        onLineRemove={() => {}}
        onLineRemoveAll={() => {}}
        onLineChange={() => {}}
        valuePerLocale={valuePerLocale}
        valuePerChannel={valuePerChannel}
      />
    );

    const header = screen.getByText('pim_asset_manager.asset.upload.list.channel');
    expect(header).not.toBeNull();
  });
});
