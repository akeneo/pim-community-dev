import '@testing-library/jest-dom';
import React from 'react';
import {fireEvent, screen, act} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import {MassDeleteModal} from '../../../../../../../front/application/component/library/MassDeleteModal';

test('it renders a confirm modal delete', async () => {
  renderWithProviders(
    <MassDeleteModal onCancel={jest.fn()} onConfirm={jest.fn()} assetFamilyIdentifier="foo" selectedAssetCount={4} />
  );

  expect(screen.getByText('pim_common.confirm_deletion')).toBeInTheDocument();
  expect(screen.getByText('pim_asset_manager.asset.mass_delete.confirm')).toBeInTheDocument();
});

test('it does not allow confirmation until the asset family identifier field is valid', async () => {
  const onConfirm = jest.fn();

  renderWithProviders(
    <MassDeleteModal
      onCancel={jest.fn()}
      onConfirm={onConfirm}
      assetFamilyIdentifier="packshot"
      selectedAssetCount={4}
    />
  );

  await act(async () => {
    fireEvent.click(screen.getByText('pim_common.delete'));
  });

  expect(screen.getByText('pim_common.delete')).toHaveAttribute('disabled');
  expect(onConfirm).not.toHaveBeenCalled();

  const input = screen.getByLabelText('pim_asset_manager.asset.mass_delete.confirm_label') as HTMLInputElement;
  fireEvent.change(input, {target: {value: 'packshot'}});

  await act(async () => {
    fireEvent.click(screen.getByText('pim_common.delete'));
  });

  expect(onConfirm).toHaveBeenCalled();
});

test('it is submitable using the Enter key (when the confirm is valid)', async () => {
  const onConfirm = jest.fn();
  renderWithProviders(
    <MassDeleteModal
      onCancel={jest.fn()}
      onConfirm={onConfirm}
      assetFamilyIdentifier="packshot"
      selectedAssetCount={4}
    />
  );

  const input = screen.getByLabelText('pim_asset_manager.asset.mass_delete.confirm_label') as HTMLInputElement;

  await act(async () => {
    fireEvent.keyDown(input, {key: 'Enter', code: 'Enter'});
  });

  expect(screen.getByText('pim_common.delete')).toHaveAttribute('disabled');
  expect(onConfirm).not.toHaveBeenCalled();

  fireEvent.change(input, {target: {value: 'packshot'}});

  await act(async () => {
    fireEvent.keyDown(input, {key: 'Enter', code: 'Enter'});
  });

  expect(onConfirm).toHaveBeenCalled();
});
