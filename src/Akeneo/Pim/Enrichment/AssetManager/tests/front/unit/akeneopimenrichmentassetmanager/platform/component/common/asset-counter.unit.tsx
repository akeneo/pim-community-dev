import * as React from 'react';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import {ThemeProvider} from 'styled-components';
import {render} from '@testing-library/react';
import '@testing-library/jest-dom/extend-expect';
import AssetCounter from 'akeneopimenrichmentassetmanager/platform/component/common/asset-counter';

test('It displays no count', () => {
  const {container} = render(
    <ThemeProvider theme={akeneoTheme}>
      <AssetCounter />
    </ThemeProvider>
  );

  expect(container).toBeEmpty();
});

// The translator is not fully working, hence it displays the translation key
// when it does not a translation for it.
test('It displays a count', () => {
  const {getByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <AssetCounter resultCount={10} />
    </ThemeProvider>
  );

  expect(getByText('pim_asset_manager.asset_collection.asset_count')).toBeInTheDocument();
});
