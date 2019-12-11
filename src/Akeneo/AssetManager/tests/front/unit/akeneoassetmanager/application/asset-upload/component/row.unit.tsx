'use strict';

import * as React from 'react';
import * as ReactDOM from 'react-dom';
import '@testing-library/jest-dom/extend-expect';
import {act, fireEvent, getByLabelText} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import Row from 'akeneoassetmanager/application/asset-upload/component/row';
import {createLineFromFilename} from 'akeneoassetmanager/application/asset-upload/utils/utils';

describe('Test row component', () => {
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
    const line = createLine('foo.png', valuePerLocale, valuePerChannel);

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <table>
            <tbody>
              <Row
                line={line}
                onLineRemove={() => null}
                onLineChange={() => null}
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
    const line = createLine('foo.png', valuePerLocale, valuePerChannel);
    const onLineChange = jest.fn();

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <table>
            <tbody>
              <Row
                line={line}
                onLineRemove={() => null}
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

  test('It renders a row with the locale editable', async () => {
    const valuePerLocale = true;
    const valuePerChannel = false;
    const line = createLine('foo-en_US.png', valuePerLocale, valuePerChannel);
    const onLineChange = jest.fn();

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <table>
            <tbody>
              <Row
                line={line}
                onLineRemove={() => null}
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

  test('It renders a row with the channel editable', async () => {
    const valuePerLocale = false;
    const valuePerChannel = true;
    const line = createLine('foo-ecommerce.png', valuePerLocale, valuePerChannel);
    const onLineChange = jest.fn();

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <table>
            <tbody>
              <Row
                line={line}
                onLineRemove={() => null}
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

  test('It renders a row and I can remove it', async () => {
    const valuePerLocale = false;
    const valuePerChannel = false;
    const line = createLine('foo.png', valuePerLocale, valuePerChannel);
    const onLineRemove = jest.fn();

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <table>
            <tbody>
              <Row
                line={line}
                onLineRemove={onLineRemove}
                onLineChange={() => null}
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
