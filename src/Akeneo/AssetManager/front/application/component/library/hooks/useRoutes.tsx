import {useCallback} from 'react';
import {useRouter} from '@akeneo-pim-community/legacy-bridge';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import {clearImageLoadingQueue} from 'akeneoassetmanager/tools/image-loader';

const useRoutes = () => {
  const {generate, redirect} = useRouter();
  const redirectToAsset = useCallback((assetFamilyIdentifier: AssetFamilyIdentifier, assetCode: AssetCode) => {
    clearImageLoadingQueue();
    redirect(
      generate('akeneo_asset_manager_asset_edit', {
        assetCode,
        assetFamilyIdentifier,
        tab: 'enrich',
      })
    );
  }, []);
  const redirectToAssetFamily = useCallback((identifier: AssetFamilyIdentifier) => {
    clearImageLoadingQueue();
    redirect(
      generate('akeneo_asset_manager_asset_family_edit', {
        identifier,
        tab: 'attribute',
      })
    );
  }, []);

  return {redirectToAsset, redirectToAssetFamily};
};

export {useRoutes};
