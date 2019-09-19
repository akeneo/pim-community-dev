import * as React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {render, fireEvent, getByText} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import {AssetPicker} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-picker';

test('It should be able to open and close the asset picket modal', () => {
  let valueToUpdate = jest.fn();

  const {getByText, queryByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <AssetPicker assetsToExclude={[]} selectedAssets={() => valueToUpdate()}/>
    </ThemeProvider>
  );

  // Click on the Add Asset Button
  expect(getByText('pim_asset_manager.asset_collection.add_asset')).toBeInTheDocument();
  fireEvent.click(getByText('pim_asset_manager.asset_collection.add_asset'));

  // Asset Picker Modal opened
  expect(getByText('pim_asset_manager.asset_picker.title')).toBeInTheDocument();

  // Click on the Confirm Button
  fireEvent.click(getByText('pim_common.confirm'));

  // Asset Picker Mssaodal closed
  expect(queryByText('pim_asset_manager.asset_picker.title')).toBeNull();
});
