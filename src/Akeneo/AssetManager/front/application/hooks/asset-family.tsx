import * as React from 'react';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {AssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {AssetFamilyResult, AssetFamilyFetcher} from 'akeneoassetmanager/infrastructure/fetcher/asset-family';

export type AssetFamilyDataProvider = {
  assetFamilyFetcher: AssetFamilyFetcher;
};

export const useAssetFamily = (
  dataProvider: AssetFamilyDataProvider,
  assetFamilyIdentifier: AssetFamilyIdentifier | null
): AssetFamily | null => {
  const [assetFamily, setAssetFamily] = React.useState<AssetFamily | null>(null);
  React.useEffect(() => {
    if (null === assetFamilyIdentifier) return;
    dataProvider.assetFamilyFetcher
      .fetch(assetFamilyIdentifier)
      .then((result: AssetFamilyResult) => setAssetFamily(result.assetFamily));
  }, [assetFamilyIdentifier]);
  return assetFamily;
};
