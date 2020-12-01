import React from 'react';
import {screen} from '@testing-library/react';
import RowStatus from 'akeneoassetmanager/application/asset-upload/component/row-status';
import {LineStatus} from 'akeneoassetmanager/application/asset-upload/model/line';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';

describe('Test row-status component', () => {
  test('It renders without errors', () => {
    renderWithProviders(<RowStatus status={LineStatus.WaitingForUpload} progress={0} />);
  });

  test('It renders the WaitingForUpload label', () => {
    const status = LineStatus.WaitingForUpload;

    renderWithProviders(<RowStatus status={status} progress={0} />);

    const label = screen.getByText('pim_asset_manager.asset.upload.status.' + status);
    expect(label).not.toBeNull();
  });

  test('It renders the Valid label', () => {
    const status = LineStatus.Valid;

    renderWithProviders(<RowStatus status={status} progress={0} />);

    const label = screen.getByText('pim_asset_manager.asset.upload.status.' + status);
    expect(label).not.toBeNull();
  });

  test('It renders the Created label', () => {
    const status = LineStatus.Created;

    renderWithProviders(<RowStatus status={status} progress={0} />);

    const label = screen.getByText('pim_asset_manager.asset.upload.status.' + status);
    expect(label).not.toBeNull();
  });

  test('It renders the Invalid label', () => {
    const status = LineStatus.Invalid;

    renderWithProviders(<RowStatus status={status} progress={0} />);

    const label = screen.getByText('pim_asset_manager.asset.upload.status.' + status);
    expect(label).not.toBeNull();
  });

  test('It renders the Uploaded label', () => {
    const status = LineStatus.Uploaded;

    renderWithProviders(<RowStatus status={status} progress={0} />);

    const label = screen.getByText('pim_asset_manager.asset.upload.status.' + status);
    expect(label).not.toBeNull();
  });

  test('It renders the UploadInProgress progress bar with the correct width', () => {
    const status = LineStatus.UploadInProgress;

    renderWithProviders(<RowStatus status={status} progress={0.4} />);

    const progressBar = screen.getByTitle('pim_asset_manager.asset.upload.status.' + status);

    expect(progressBar).not.toBeNull();
    expect(progressBar.getAttribute('width')).toEqual('40');
  });

  test('It renders the UploadInProgress progress bar even with a progress too low', () => {
    const status = LineStatus.UploadInProgress;

    renderWithProviders(<RowStatus status={status} progress={-0.4} />);

    const progressBar = screen.getByTitle('pim_asset_manager.asset.upload.status.' + status);

    expect(progressBar).not.toBeNull();
    expect(progressBar.getAttribute('width')).toEqual('0');
  });

  test('It renders the UploadInProgress progress bar even with a progress too high', () => {
    const status = LineStatus.UploadInProgress;

    renderWithProviders(<RowStatus status={status} progress={1.4} />);

    const progressBar = screen.getByTitle('pim_asset_manager.asset.upload.status.' + status);

    expect(progressBar).not.toBeNull();
    expect(progressBar.getAttribute('width')).toEqual('100');
  });

  test('It throws with an unknown status', () => {
    jest.spyOn(console, 'error').mockImplementation(() => {});

    expect(() => {
      renderWithProviders(<RowStatus status={'something_else'} progress={0} />);
    }).toThrow();
  });
});
