'use strict';

import React from 'react';
import * as ReactDOM from 'react-dom';
import '@testing-library/jest-dom/extend-expect';
import {wait, act, fireEvent, getByLabelText, getByTitle, getByText} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import UploadModal from 'akeneoassetmanager/application/asset-upload/component/modal';
import Line, {LineStatus} from 'akeneoassetmanager/application/asset-upload/model/line';
import {createFakeAssetFamily} from '../tools';
import Channel from 'akeneoassetmanager/domain/model/channel';
import Locale from 'akeneoassetmanager/domain/model/locale';
import {uploadFile} from 'akeneoassetmanager/application/asset-upload/utils/file';

jest.mock('akeneoassetmanager/application/component/app/select2');
jest.mock('akeneoassetmanager/tools/notify', () => jest.fn());

jest.mock('akeneoassetmanager/application/asset-upload/saver/asset', () => ({
  create: jest.fn().mockImplementation(() => Promise.resolve(null)),
}));
jest.mock('akeneoassetmanager/application/asset-upload/utils/file', () => ({
  uploadFile: jest.fn().mockImplementation((file: File) =>
    Promise.resolve({
      filePath: file.name,
      originalFilename: file.name,
    })
  ),
  getThumbnailFromFile: jest.fn().mockImplementation((file: File, line: Line) =>
    Promise.resolve({
      thumbnail: '/tmb/' + file.name,
      line: line,
    })
  ),
}));

describe('Test modal component', () => {
  let container: HTMLElement;

  beforeEach(() => {
    container = document.createElement('div');
    document.body.appendChild(container);
  });

  afterEach(() => {
    document.body.removeChild(container);
  });

  test('It renders without errors', async () => {
    const assetFamily = createFakeAssetFamily(false, false);
    const channels: Channel[] = [];
    const locales: Locale[] = [];

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <UploadModal
            confirmLabel="pim_asset_manager.asset.upload.confirm"
            locale="en_US"
            assetFamily={assetFamily}
            channels={channels}
            locales={locales}
            onCancel={() => {}}
            onAssetCreated={() => {}}
          />
        </ThemeProvider>,
        container
      );
    });
  });

  test('I can close the modal', async () => {
    const assetFamily = createFakeAssetFamily(false, false);
    const channels: Channel[] = [];
    const locales: Locale[] = [];
    const onCancel = jest.fn();

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <UploadModal
            confirmLabel="pim_asset_manager.asset.upload.confirm"
            locale="en_US"
            assetFamily={assetFamily}
            channels={channels}
            locales={locales}
            onCancel={onCancel}
            onAssetCreated={() => {}}
          />
        </ThemeProvider>,
        container
      );
    });

    const button = getByLabelText(container, 'pim_asset_manager.close');
    fireEvent.click(button);
    expect(onCancel).toHaveBeenCalled();
  });

  test('I can drop a file and create the asset', async () => {
    const assetFamily = createFakeAssetFamily(false, false);
    const channels: Channel[] = [];
    const locales: Locale[] = [];
    const onAssetCreated = jest.fn();

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <UploadModal
            confirmLabel="pim_asset_manager.asset.upload.confirm"
            locale="en_US"
            assetFamily={assetFamily}
            channels={channels}
            locales={locales}
            onCancel={() => {}}
            onAssetCreated={onAssetCreated}
          />
        </ThemeProvider>,
        container
      );
    });

    const files = [new File(['foo'], 'foo.png', {type: 'image/png'})];

    await act(async () => {
      const filesInput = getByLabelText(container, 'pim_asset_manager.asset.upload.drop_or_click_here');
      fireEvent.change(filesInput, {target: {files: files}});

      // Wait for the line to be Valid (uploaded & completed)
      await wait(() => getByText(container, 'pim_asset_manager.asset.upload.status.' + LineStatus.Valid));

      const confirmButton = getByTitle(container, 'pim_asset_manager.asset.upload.confirm');
      fireEvent.click(confirmButton);

      // Wait for the line to be Created
      await wait(() => getByText(container, 'pim_asset_manager.asset.upload.status.' + LineStatus.Created));
    });
  });

  test('I can drop a file and dispatch its code being changed', async () => {
    const assetFamily = createFakeAssetFamily(false, false);
    const channels: Channel[] = [];
    const locales: Locale[] = [];

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <UploadModal
            confirmLabel="pim_asset_manager.asset.upload.confirm"
            locale="en_US"
            assetFamily={assetFamily}
            channels={channels}
            locales={locales}
            onCancel={() => {}}
            onAssetCreated={() => {}}
          />
        </ThemeProvider>,
        container
      );
    });

    const files = [new File(['foo'], 'foo.png', {type: 'image/png'})];

    await act(async () => {
      const filesInput = getByLabelText(container, 'pim_asset_manager.asset.upload.drop_or_click_here');
      fireEvent.change(filesInput, {target: {files: files}});

      // Wait for the line to be Valid (uploaded & completed)
      await wait(() => getByText(container, 'pim_asset_manager.asset.upload.status.' + LineStatus.Valid));

      const codeInput = getByLabelText(container, 'pim_asset_manager.asset.upload.list.code') as HTMLInputElement;
      fireEvent.change(codeInput, {target: {value: 'foobar'}});

      // There should be a way to test if the dispatch has been called there
      // or a better way to cover this event
    });
  });

  test('I can drop a file and dispatch a line being removed', async () => {
    const assetFamily = createFakeAssetFamily(false, false);
    const channels: Channel[] = [];
    const locales: Locale[] = [];

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <UploadModal
            confirmLabel="pim_asset_manager.asset.upload.confirm"
            locale="en_US"
            assetFamily={assetFamily}
            channels={channels}
            locales={locales}
            onCancel={() => {}}
            onAssetCreated={() => {}}
          />
        </ThemeProvider>,
        container
      );
    });

    const files = [new File(['foo'], 'foo.png', {type: 'image/png'})];

    await act(async () => {
      const filesInput = getByLabelText(container, 'pim_asset_manager.asset.upload.drop_or_click_here');
      fireEvent.change(filesInput, {target: {files: files}});

      // Wait for the line to be Valid (uploaded & completed)
      await wait(() => getByText(container, 'pim_asset_manager.asset.upload.status.' + LineStatus.Valid));

      const removeLineButton = getByLabelText(container, 'pim_asset_manager.asset.upload.remove');
      fireEvent.click(removeLineButton);

      // There should be a way to test if the dispatch has been called there
      // or a better way to cover this event
    });
  });

  test('I can drop a file and dispatch all the lines being removed', async () => {
    const assetFamily = createFakeAssetFamily(false, false);
    const channels: Channel[] = [];
    const locales: Locale[] = [];

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <UploadModal
            confirmLabel="pim_asset_manager.asset.upload.confirm"
            locale="en_US"
            assetFamily={assetFamily}
            channels={channels}
            locales={locales}
            onCancel={() => {}}
            onAssetCreated={() => {}}
          />
        </ThemeProvider>,
        container
      );
    });

    const files = [new File(['foo'], 'foo.png', {type: 'image/png'})];

    await act(async () => {
      const filesInput = getByLabelText(container, 'pim_asset_manager.asset.upload.drop_or_click_here');
      fireEvent.change(filesInput, {target: {files: files}});

      // Wait for the line to be Valid (uploaded & completed)
      await wait(() => getByText(container, 'pim_asset_manager.asset.upload.status.' + LineStatus.Valid));

      const removeAllLinesButton = getByText(container, 'pim_asset_manager.asset.upload.remove_all');
      fireEvent.click(removeAllLinesButton);

      // There should be a way to test if the dispatch has been called there
      // or a better way to cover this event
    });
  });

  test('I can drop a file and dispatch an upload retry', async () => {
    uploadFile.mockImplementationOnce(() => Promise.reject());
    const assetFamily = createFakeAssetFamily(false, false);
    const channels: Channel[] = [];
    const locales: Locale[] = [];

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <UploadModal
            locale="en_US"
            assetFamily={assetFamily}
            channels={channels}
            locales={locales}
            onCancel={() => {}}
            onAssetCreated={() => {}}
          />
        </ThemeProvider>,
        container
      );
    });

    const files = [new File(['foo'], 'foo.png', {type: 'image/png'})];

    await act(async () => {
      const filesInput = getByLabelText(container, 'pim_asset_manager.asset.upload.drop_or_click_here');
      fireEvent.change(filesInput, {target: {files: files}});

      // Wait for the line to be Invalid (uploaded failed)
      await wait(() => getByText(container, 'pim_asset_manager.asset.upload.status.' + LineStatus.Invalid));

      const retryUploadButton = getByLabelText(container, 'pim_asset_manager.asset.upload.retry');
      fireEvent.click(retryUploadButton);

      // There should be a way to test if the dispatch has been called there
      // or a better way to cover this event
    });
  });
});
