import React from 'react';
import {act, screen, fireEvent, waitFor} from '@testing-library/react';
import {CreateAssetFamilyModal} from 'akeneoassetmanager/application/component/asset-family/create';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';

// This mock throws an error if the code is 'throw'
jest.mock('akeneoassetmanager/infrastructure/saver/asset-family', () => ({
  create: jest.fn().mockImplementation(
    ({code}: {code: string}) =>
      new Promise(resolve => {
        if ('' === code) {
          resolve([{messageTemplate: 'Code should not be empty', propertyPath: 'code'}]);
        }
        if ('throw' === code) {
          throw Error('Error');
        }
        resolve(null);
      })
  ),
}));

const localeCode = 'en_US';

describe('Test Asset Family create modal component', () => {
  test('It displays the Create Asset Family modal without errors', () => {
    renderWithProviders(<CreateAssetFamilyModal locale={localeCode} />);
  });

  test('I can create a single asset family with valid parameters', async () => {
    let assetFamilyCode = '';
    const onAssetFamilyCreated = jest.fn().mockImplementation((code: string) => {
      assetFamilyCode = code;
    });
    renderWithProviders(<CreateAssetFamilyModal locale={localeCode} onAssetFamilyCreated={onAssetFamilyCreated} />);

    const codeInput = document.getElementById('pim_asset_manager.asset_family.create.input.code') as HTMLInputElement;
    const labelInput = document.getElementById('pim_asset_manager.asset_family.create.input.label') as HTMLInputElement;
    const submitButton = screen.getByText('pim_asset_manager.asset_family.create.confirm');

    await act(async () => {
      await waitFor(() => fireEvent.change(codeInput, {target: {value: 'fakeCode'}}));
      await waitFor(() => fireEvent.change(labelInput, {target: {value: 'nice label'}}));
      fireEvent.click(submitButton);
      // Two clicks to check that onAssetFamilyCreated callback is called only once
      fireEvent.click(submitButton);
    });

    expect(onAssetFamilyCreated).toHaveBeenCalledTimes(1);
    expect(assetFamilyCode).toEqual('fakeCode');
  });

  test('I can not create a single asset family with invalid parameters', async () => {
    const onAssetFamilyCreated = jest.fn();
    renderWithProviders(<CreateAssetFamilyModal locale={localeCode} onAssetFamilyCreated={onAssetFamilyCreated} />);

    const codeInput = document.getElementById('pim_asset_manager.asset_family.create.input.code') as HTMLInputElement;
    const labelInput = document.getElementById('pim_asset_manager.asset_family.create.input.label') as HTMLInputElement;
    const submitButton = screen.getByText('pim_asset_manager.asset_family.create.confirm');

    await act(async () => {
      fireEvent.change(codeInput, {target: {value: null}});
      fireEvent.change(labelInput, {target: {value: null}});
      fireEvent.click(submitButton);
    });

    expect(onAssetFamilyCreated).not.toHaveBeenCalled();
    expect(screen.getByText('Code should not be empty')).toBeInTheDocument();
  });

  test('It catches errors during Asset Family creation', async () => {
    const onAssetFamilyCreated = jest.fn();
    renderWithProviders(<CreateAssetFamilyModal locale={localeCode} onAssetFamilyCreated={onAssetFamilyCreated} />);

    const codeInput = document.getElementById('pim_asset_manager.asset_family.create.input.code') as HTMLInputElement;
    const submitButton = screen.getByText('pim_asset_manager.asset_family.create.confirm');

    await act(async () => {
      await waitFor(() => fireEvent.change(codeInput, {target: {value: 'throw'}}));
      fireEvent.click(submitButton);
    });

    expect(onAssetFamilyCreated).not.toHaveBeenCalled();
  });
});
