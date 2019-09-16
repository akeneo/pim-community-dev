import * as React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {render, fireEvent, getByText} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import {AssetPicker} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-picker';

test('It should display the asset picker in a modal', () => {
  const toggleAssetPicker = jest.fn();

  const {getByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <AssetPicker show={true} onClose={() => toggleAssetPicker(false)}/>
    </ThemeProvider>
  );

  expect(getByText('pim_asset_manager.asset_picker.title')).toBeInTheDocument();
});

test('It should be able to render the asset picker modal and close it', () => {
  const toggleAssetPicker = jest.fn();

  const {getByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <React.Fragment>
        <AssetPicker show={true} onClose={toggleAssetPicker} />
      </React.Fragment>
    </ThemeProvider>
  );

  expect(getByText('pim_asset_manager.asset_picker.confirm')).toBeInTheDocument();
  fireEvent.click(getByText('pim_asset_manager.asset_picker.confirm'));
  expect(toggleAssetPicker).toHaveBeenCalledTimes(1);
});

test('It should not display the asset picker when the show props is set to false', () => {
  const toggleAssetPicker = jest.fn();

  const {container} = render(
    <ThemeProvider theme={akeneoTheme}>
      <React.Fragment>
        <AssetPicker show={false} onClose={toggleAssetPicker} />
      </React.Fragment>
    </ThemeProvider>
  );

  expect(container).toBeEmpty();
});
