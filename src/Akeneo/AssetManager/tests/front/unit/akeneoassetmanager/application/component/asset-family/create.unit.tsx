import * as React from 'react';
import * as ReactDOM from 'react-dom';
import {act, getByText, fireEvent, wait} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import {CreateAssetFamilyModal} from 'akeneoassetmanager/application/component/asset-family/create';

// This mock throws an error if the code is 'throw'
jest.mock('akeneoassetmanager/infrastructure/saver/asset-family', () => ({
  create: jest.fn().mockImplementation(
    ({code}: {code: string}) =>
      new Promise(resolve => {
        if ('' === code) {
          resolve([{message: 'Code should not be empty', propertyPath: 'code'}]);
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
  let container: HTMLElement;

  beforeEach(() => {
    container = document.createElement('div');
    document.body.appendChild(container);
  });

  afterEach(() => {
    document.body.removeChild(container);
  });

  test('It displays the Create Asset Family modal without errors', async () => {
    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <CreateAssetFamilyModal locale={localeCode} />
        </ThemeProvider>,
        container
      );
    });
  });

  test('I can create a single asset family with valid parameters', async () => {
    let assetFamilyCode = '';
    const onAssetFamilyCreated = jest.fn().mockImplementation((code: string) => {
      assetFamilyCode = code;
    });
    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <CreateAssetFamilyModal locale={localeCode} onAssetFamilyCreated={onAssetFamilyCreated} />
        </ThemeProvider>,
        container
      );
    });

    const codeInput = document.getElementById('pim_asset_manager.asset_family.create.input.code') as HTMLInputElement;
    const labelInput = document.getElementById('pim_asset_manager.asset_family.create.input.label') as HTMLInputElement;
    const submitButton = getByText(container, 'pim_asset_manager.asset_family.create.confirm');

    await act(async () => {
      await wait(() => fireEvent.change(codeInput, {target: {value: 'fakeCode'}}));
      await wait(() => fireEvent.change(labelInput, {target: {value: 'nice label'}}));
      fireEvent.click(submitButton);
      // Two clicks to check that onAssetFamilyCreated callback is called only once
      fireEvent.click(submitButton);
    });

    expect(onAssetFamilyCreated).toHaveBeenCalledTimes(1);
    expect(assetFamilyCode).toEqual('fakeCode');
  });

  test('I can not create a single asset family with invalid parameters', async () => {
    const onAssetFamilyCreated = jest.fn();
    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <CreateAssetFamilyModal locale={localeCode} onAssetFamilyCreated={onAssetFamilyCreated} />
        </ThemeProvider>,
        container
      );
    });

    const codeInput = document.getElementById('pim_asset_manager.asset_family.create.input.code') as HTMLInputElement;
    const labelInput = document.getElementById('pim_asset_manager.asset_family.create.input.label') as HTMLInputElement;
    const submitButton = getByText(container, 'pim_asset_manager.asset_family.create.confirm');

    await act(async () => {
      fireEvent.change(codeInput, {target: {value: null}});
      fireEvent.change(labelInput, {target: {value: null}});
      fireEvent.click(submitButton);
    });

    const errors = document.querySelectorAll('.error-message');

    expect(onAssetFamilyCreated).not.toHaveBeenCalled();
    expect(errors.length).toBeGreaterThan(0);
  });

  test('It catches errors during Asset Family creation', async () => {
    const onAssetFamilyCreated = jest.fn();
    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <CreateAssetFamilyModal locale={localeCode} onAssetFamilyCreated={onAssetFamilyCreated} />
        </ThemeProvider>,
        container
      );
    });

    const codeInput = document.getElementById('pim_asset_manager.asset_family.create.input.code') as HTMLInputElement;
    const submitButton = getByText(container, 'pim_asset_manager.asset_family.create.confirm');

    await act(async () => {
      await wait(() => fireEvent.change(codeInput, {target: {value: 'throw'}}));
      fireEvent.click(submitButton);
    });

    expect(onAssetFamilyCreated).not.toHaveBeenCalled();
  });
});
