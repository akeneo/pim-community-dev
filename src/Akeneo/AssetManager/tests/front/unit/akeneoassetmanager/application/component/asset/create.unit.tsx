import React from 'react';
import {act, screen, fireEvent, waitFor} from '@testing-library/react';
import {CreateModal} from 'akeneoassetmanager/application/component/asset/create';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';

// This mock throws an error if the code is 'throw'
jest.mock('akeneoassetmanager/infrastructure/saver/asset', () => ({
  create: jest.fn().mockImplementation(
    ({code}: {code: string}) =>
      new Promise(resolve => {
        if ('' === code) {
          resolve([{messageTemplate: 'Code should not be empty', propertyPath: 'code', parameters: {}}]);
        }
        if ('throw' === code) {
          throw Error('Error');
        }
        resolve(null);
      })
  ),
}));

const assetFamily = {
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
      value_per_locale: true,
      value_per_channel: true,
    },
  ],
  transformations: '[]',
};
const localeCode = 'en_US';

describe('Test Asset create modal component', () => {
  test('It displays the Create Asset modal without errors', () => {
    renderWithProviders(<CreateModal assetFamily={assetFamily} locale={localeCode} />);
  });

  test('I can set a code & a label', () => {
    renderWithProviders(<CreateModal assetFamily={assetFamily} locale={localeCode} />);

    const codeInput = screen.getByLabelText('pim_asset_manager.asset.create.input.code') as HTMLInputElement;
    const labelInput = screen.getByLabelText('pim_asset_manager.asset.create.input.label') as HTMLInputElement;

    expect(codeInput.value).toBe('');
    fireEvent.change(codeInput, {target: {value: 'foobar'}});
    expect(codeInput.value).toBe('foobar');

    expect(labelInput.value).toBe('');
    fireEvent.change(labelInput, {target: {value: 'another one'}});
    expect(labelInput.value).toBe('another one');
  });

  test('I can create a single asset with valid parameters', async () => {
    let assetCode = '';
    let createAnother = false;
    const onAssetCreated = jest.fn().mockImplementation((code: string, anotherOne: boolean) => {
      assetCode = code;
      createAnother = anotherOne;
    });
    renderWithProviders(<CreateModal assetFamily={assetFamily} locale={localeCode} onAssetCreated={onAssetCreated} />);

    const codeInput = screen.getByLabelText('pim_asset_manager.asset.create.input.code') as HTMLInputElement;
    const labelInput = screen.getByLabelText('pim_asset_manager.asset.create.input.label') as HTMLInputElement;
    const submitButton = screen.getByText('pim_common.save');

    await act(async () => {
      await waitFor(() => fireEvent.change(codeInput, {target: {value: 'fakeCode'}}));
      await waitFor(() => fireEvent.change(labelInput, {target: {value: 'nice label'}}));
      fireEvent.click(submitButton);
      // Two clicks to check that onAssetCreated callback is called only once
      fireEvent.click(submitButton);
    });

    expect(onAssetCreated).toHaveBeenCalledTimes(1);
    expect(assetCode).toEqual('fakeCode');
    expect(createAnother).toBe(false);
  });

  test('I can create several assets', async () => {
    const onAssetCreated = jest.fn();

    renderWithProviders(<CreateModal assetFamily={assetFamily} locale={localeCode} onAssetCreated={onAssetCreated} />);

    const codeInput = screen.getByLabelText('pim_asset_manager.asset.create.input.code') as HTMLInputElement;
    const labelInput = screen.getByLabelText('pim_asset_manager.asset.create.input.label') as HTMLInputElement;
    const submitButton = screen.getByText('pim_common.save');

    await act(async () => {
      await waitFor(() => fireEvent.change(codeInput, {target: {value: 'fakeCode'}}));
      await waitFor(() => fireEvent.change(labelInput, {target: {value: 'nice label'}}));
      fireEvent.click(screen.getByLabelText('pim_asset_manager.asset.create.input.create_another'));
      fireEvent.click(submitButton);
      // Two clicks to check that onAssetCreated callback is called only once
      fireEvent.click(submitButton);
    });

    expect(onAssetCreated).toHaveBeenCalledTimes(1);
    expect(onAssetCreated).toHaveBeenCalledWith('fakeCode', true);

    expect(codeInput.value).toEqual('');
    expect(labelInput.value).toEqual('');
  });

  test('I can not create a single asset with invalid parameters', async () => {
    const onAssetCreated = jest.fn();
    renderWithProviders(<CreateModal assetFamily={assetFamily} locale={localeCode} onAssetCreated={onAssetCreated} />);

    const codeInput = screen.getByLabelText('pim_asset_manager.asset.create.input.code') as HTMLInputElement;
    const labelInput = screen.getByLabelText('pim_asset_manager.asset.create.input.label') as HTMLInputElement;
    const submitButton = screen.getByText('pim_common.save');

    await act(async () => {
      fireEvent.change(codeInput, {target: {value: null}});
      fireEvent.change(labelInput, {target: {value: null}});
      fireEvent.click(submitButton);
    });

    expect(onAssetCreated).not.toHaveBeenCalled();
    expect(screen.getByText('Code should not be empty')).toBeInTheDocument();
  });

  test('It catches errors during Asset creation', async () => {
    const onAssetCreated = jest.fn();
    renderWithProviders(<CreateModal assetFamily={assetFamily} locale={localeCode} onAssetCreated={onAssetCreated} />);

    const codeInput = screen.getByLabelText('pim_asset_manager.asset.create.input.code') as HTMLInputElement;
    const submitButton = screen.getByText('pim_common.save');

    await act(async () => {
      await waitFor(() => fireEvent.change(codeInput, {target: {value: 'throw'}}));
      fireEvent.click(submitButton);
    });

    expect(onAssetCreated).not.toHaveBeenCalled();
  });
});
