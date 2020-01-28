import * as React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {render, fireEvent} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import EmptyResult from 'akeneoassetmanager/application/component/asset/list/mosaic/empty-result';

test('It displays a notification', () => {
  const {getByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <EmptyResult maxSelectionCount={2000} />
    </ThemeProvider>
  );
  expect(getByText('pim_asset_manager.asset_picker.no_result.title')).toBeInTheDocument();
  expect(getByText('pim_asset_manager.asset_picker.no_result.sub_title')).toBeInTheDocument();
});
