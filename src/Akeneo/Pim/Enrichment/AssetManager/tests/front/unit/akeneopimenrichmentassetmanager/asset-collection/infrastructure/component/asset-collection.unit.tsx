import * as React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {render} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from 'akeneopimenrichmentassetmanager/platform/component/theme';
import {AssetCollection} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-collection';
import {fetchAssetCollection} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/fetcher/asset';

jest.mock('pim/router', () => {});
jest.mock('routing', () => {});
fetchAssetCollection = jest.fn();

test('It should render an asset collection', () => {
  const assetFamilyIdentifier = 'packshot';
  const assetCodes = ['iphone', 'honor'];
  const readOnly = true;
  const context = {channel: 'ecommerce', locale: 'en_US'};
  fetchAssetCollection.mockImplementation(() => getMockAssetCollection());
  const {debug} = render(
    <ThemeProvider theme={akeneoTheme}>
      <AssetCollection
        assetFamilyIdentifier={assetFamilyIdentifier}
        assetCodes={assetCodes}
        readOnly={readOnly}
        context={context}
      />
    </ThemeProvider>
  );
  debug();
});

const getMockAssetCollection = () => {

};
