import * as React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {render, fireEvent} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import MaximumSelectionReachedNotification from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-picker/mosaic/maximum-selection-reached-notification';

test('It displays a notification', () => {
  const {getByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <MaximumSelectionReachedNotification maxSelectionCount={2000} />
    </ThemeProvider>
  );
  expect(getByText('pim_asset_manager.asset_picker.notification.maximum_selection_reached')).toBeInTheDocument();
});
