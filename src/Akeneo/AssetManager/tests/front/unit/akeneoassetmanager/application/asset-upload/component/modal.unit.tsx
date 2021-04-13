import React from 'react';
import {act, fireEvent, screen, waitFor} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import {UploadModal} from 'akeneoassetmanager/application/asset-upload/component/modal';
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

const assetFamily = createFakeAssetFamily(false, false);
const channels: Channel[] = [];
const locales: Locale[] = [];

describe('Test modal component', () => {
  test('It renders without errors', () => {
    renderWithProviders(
      <UploadModal
        confirmLabel="pim_asset_manager.asset.upload.confirm"
        locale="en_US"
        assetFamily={assetFamily}
        channels={channels}
        locales={locales}
        onCancel={jest.fn()}
        onAssetCreated={jest.fn()}
      />
    );

    expect(screen.getByText('pim_asset_manager.asset.upload.confirm')).toBeInTheDocument();
  });

  test('I can close the modal', () => {
    const onCancel = jest.fn();

    renderWithProviders(
      <UploadModal
        confirmLabel="pim_asset_manager.asset.upload.confirm"
        locale="en_US"
        assetFamily={assetFamily}
        channels={channels}
        locales={locales}
        onCancel={onCancel}
        onAssetCreated={jest.fn()}
      />
    );

    fireEvent.click(screen.getByTitle('pim_common.close'));

    expect(onCancel).toHaveBeenCalled();
  });

  test('I can drop a file and create the asset', async () => {
    const onAssetCreated = jest.fn();

    renderWithProviders(
      <UploadModal
        confirmLabel="pim_asset_manager.asset.upload.confirm"
        locale="en_US"
        assetFamily={assetFamily}
        channels={channels}
        locales={locales}
        onCancel={jest.fn()}
        onAssetCreated={onAssetCreated}
      />
    );

    const files = [new File(['foo'], 'foo.png', {type: 'image/png'})];

    await act(async () => {
      const filesInput = screen.getByLabelText('pim_asset_manager.asset.upload.drop_or_click_here');
      fireEvent.change(filesInput, {target: {files}});

      // Wait for the line to be Valid (uploaded & completed)
      await waitFor(() => screen.getByText('pim_asset_manager.asset.upload.status.' + LineStatus.Valid));

      const confirmButton = screen.getByText('pim_asset_manager.asset.upload.confirm');
      fireEvent.click(confirmButton);

      // Wait for the line to be Created
      await waitFor(() => screen.getByText('pim_asset_manager.asset.upload.status.' + LineStatus.Created));
    });
  });

  test('I can drop a file and dispatch its code being changed', async () => {
    renderWithProviders(
      <UploadModal
        confirmLabel="pim_asset_manager.asset.upload.confirm"
        locale="en_US"
        assetFamily={assetFamily}
        channels={channels}
        locales={locales}
        onCancel={jest.fn()}
        onAssetCreated={jest.fn()}
      />
    );

    const files = [new File(['foo'], 'foo.png', {type: 'image/png'})];

    await act(async () => {
      const filesInput = screen.getByLabelText('pim_asset_manager.asset.upload.drop_or_click_here');
      fireEvent.change(filesInput, {target: {files: files}});

      // Wait for the line to be Valid (uploaded & completed)
      await waitFor(() => screen.getByText('pim_asset_manager.asset.upload.status.' + LineStatus.Valid));

      const codeInput = screen.getByLabelText('pim_asset_manager.asset.upload.list.code') as HTMLInputElement;
      fireEvent.change(codeInput, {target: {value: 'foobar'}});

      // There should be a way to test if the dispatch has been called there
      // or a better way to cover this event
    });
  });

  test('I can drop a file and dispatch a line being removed', async () => {
    renderWithProviders(
      <UploadModal
        confirmLabel="pim_asset_manager.asset.upload.confirm"
        locale="en_US"
        assetFamily={assetFamily}
        channels={channels}
        locales={locales}
        onCancel={jest.fn()}
        onAssetCreated={jest.fn()}
      />
    );

    const files = [new File(['foo'], 'foo.png', {type: 'image/png'})];

    await act(async () => {
      const filesInput = screen.getByLabelText('pim_asset_manager.asset.upload.drop_or_click_here');
      fireEvent.change(filesInput, {target: {files: files}});

      // Wait for the line to be Valid (uploaded & completed)
      await waitFor(() => screen.getByText('pim_asset_manager.asset.upload.status.' + LineStatus.Valid));

      const removeLineButton = screen.getByTitle('pim_asset_manager.asset.upload.remove');
      fireEvent.click(removeLineButton);

      // There should be a way to test if the dispatch has been called there
      // or a better way to cover this event
    });
  });

  test('I can drop a file and dispatch all the lines being removed', async () => {
    renderWithProviders(
      <UploadModal
        confirmLabel="pim_asset_manager.asset.upload.confirm"
        locale="en_US"
        assetFamily={assetFamily}
        channels={channels}
        locales={locales}
        onCancel={jest.fn()}
        onAssetCreated={jest.fn()}
      />
    );

    const files = [new File(['foo'], 'foo.png', {type: 'image/png'})];

    await act(async () => {
      const filesInput = screen.getByLabelText('pim_asset_manager.asset.upload.drop_or_click_here');
      fireEvent.change(filesInput, {target: {files: files}});

      // Wait for the line to be Valid (uploaded & completed)
      await waitFor(() => screen.getByText('pim_asset_manager.asset.upload.status.' + LineStatus.Valid));

      const removeAllLinesButton = screen.getByText('pim_asset_manager.asset.upload.remove_all');
      fireEvent.click(removeAllLinesButton);

      // There should be a way to test if the dispatch has been called there
      // or a better way to cover this event
    });
  });

  test('I can drop a file and dispatch an upload retry', async () => {
    uploadFile.mockImplementationOnce(() => Promise.reject());

    renderWithProviders(
      <UploadModal
        confirmLabel="pim_asset_manager.asset.upload.confirm"
        locale="en_US"
        assetFamily={assetFamily}
        channels={channels}
        locales={locales}
        onCancel={jest.fn()}
        onAssetCreated={jest.fn()}
      />
    );

    const files = [new File(['foo'], 'foo.png', {type: 'image/png'})];

    await act(async () => {
      const filesInput = screen.getByLabelText('pim_asset_manager.asset.upload.drop_or_click_here');
      fireEvent.change(filesInput, {target: {files: files}});

      // Wait for the line to be Invalid (uploaded failed)
      await waitFor(() => screen.getByText('pim_asset_manager.asset.upload.status.' + LineStatus.Invalid));

      const retryUploadButton = screen.getByTitle('pim_asset_manager.asset.upload.retry');
      fireEvent.click(retryUploadButton);

      // There should be a way to test if the dispatch has been called there
      // or a better way to cover this event
    });
  });
});
