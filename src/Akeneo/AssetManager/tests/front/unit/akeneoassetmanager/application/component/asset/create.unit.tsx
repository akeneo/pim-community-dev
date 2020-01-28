import * as React from 'react';
import * as ReactDOM from 'react-dom';
import {act, getByText, fireEvent, wait} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import {CreateModal} from 'akeneoassetmanager/application/component/asset/create';

// This mock throws an error if the code is 'throw'
jest.mock('akeneoassetmanager/infrastructure/saver/asset', () => ({
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
  let container: HTMLElement;

  beforeEach(() => {
    container = document.createElement('div');
    document.body.appendChild(container);
  });

  afterEach(() => {
    document.body.removeChild(container);
  });

  test('It displays the Create Asset modal without errors', async () => {
    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <CreateModal assetFamily={assetFamily} locale={localeCode} />
        </ThemeProvider>,
        container
      );
    });
  });

  test('I can choose to create another asset by checking the createAnother checkbox', async () => {
    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <CreateModal assetFamily={assetFamily} locale={localeCode} />
        </ThemeProvider>,
        container
      );
    });

    const checkbox = document.getElementById('pim_asset_manager.asset.create.input.create_another');
    const checkboxLabel = getByText(container, 'pim_asset_manager.asset.create.input.create_another');

    expect(checkbox.getAttribute('data-checked')).toBe('false');
    fireEvent.click(checkbox);
    expect(checkbox.getAttribute('data-checked')).toBe('true');
    fireEvent.click(checkboxLabel);
    expect(checkbox.getAttribute('data-checked')).toBe('false');
  });

  test('I can set a code & a label', async () => {
    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <CreateModal assetFamily={assetFamily} locale={localeCode} />
        </ThemeProvider>,
        container
      );
    });

    const codeInput = document.getElementById('pim_asset_manager.asset.create.input.code') as HTMLInputElement;
    const labelInput = document.getElementById('pim_asset_manager.asset.create.input.label') as HTMLInputElement;

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
    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <CreateModal assetFamily={assetFamily} locale={localeCode} onAssetCreated={onAssetCreated} />
        </ThemeProvider>,
        container
      );
    });

    const codeInput = document.getElementById('pim_asset_manager.asset.create.input.code') as HTMLInputElement;
    const labelInput = document.getElementById('pim_asset_manager.asset.create.input.label') as HTMLInputElement;
    const submitButton = getByText(container, 'pim_asset_manager.asset.create.confirm');

    await act(async () => {
      await wait(() => fireEvent.change(codeInput, {target: {value: 'fakeCode'}}));
      await wait(() => fireEvent.change(labelInput, {target: {value: 'nice label'}}));
      fireEvent.click(submitButton);
      // Two clicks to check that onAssetCreated callback is called only once
      fireEvent.click(submitButton);
    });

    expect(onAssetCreated).toHaveBeenCalledTimes(1);
    expect(assetCode).toEqual('fakeCode');
    expect(createAnother).toBe(false);
  });

  test('I can not create a single asset with invalid parameters', async () => {
    const onAssetCreated = jest.fn();
    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <CreateModal assetFamily={assetFamily} locale={localeCode} onAssetCreated={onAssetCreated} />
        </ThemeProvider>,
        container
      );
    });

    const codeInput = document.getElementById('pim_asset_manager.asset.create.input.code') as HTMLInputElement;
    const labelInput = document.getElementById('pim_asset_manager.asset.create.input.label') as HTMLInputElement;
    const submitButton = getByText(container, 'pim_asset_manager.asset.create.confirm');

    await act(async () => {
      fireEvent.change(codeInput, {target: {value: null}});
      fireEvent.change(labelInput, {target: {value: null}});
      fireEvent.click(submitButton);
    });

    const errors = document.querySelectorAll('.error-message');

    expect(onAssetCreated).not.toHaveBeenCalled();
    expect(errors.length).toBeGreaterThan(0);
  });

  test('It catches errors during Asset creation', async () => {
    const onAssetCreated = jest.fn();
    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <CreateModal assetFamily={assetFamily} locale={localeCode} onAssetCreated={onAssetCreated} />
        </ThemeProvider>,
        container
      );
    });

    const codeInput = document.getElementById('pim_asset_manager.asset.create.input.code') as HTMLInputElement;
    const submitButton = getByText(container, 'pim_asset_manager.asset.create.confirm');

    await act(async () => {
      await wait(() => fireEvent.change(codeInput, {target: {value: 'throw'}}));
      fireEvent.click(submitButton);
    });

    expect(onAssetCreated).not.toHaveBeenCalled();
  });
});
