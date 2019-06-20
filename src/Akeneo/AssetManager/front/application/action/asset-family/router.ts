import {redirectToRoute} from 'akeneoassetmanager/application/event/router';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';

export const redirectToAssetFamily = (assetFamilyIdentifier: AssetFamilyIdentifier, tab: string) => {
  return redirectToRoute('akeneo_asset_manager_asset_family_edit', {
    identifier: assetFamilyIdentifier.stringValue(),
    tab,
  });
};

export const redirectToAssetFamilyListItem = () => {
  return redirectToRoute('akeneo_asset_manager_asset_family_index');
};
