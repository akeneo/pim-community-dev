import * as React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {render, fireEvent} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import {AssetCollectionLimitNotification} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-collection/asset-collection-limit-notification';

test('It displays a notification', () => {
  const {getByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <AssetCollectionLimitNotification />
    </ThemeProvider>
  );
  expect(getByText('pim_asset_manager.asset_collection.notification.limit')).toBeInTheDocument();
});
