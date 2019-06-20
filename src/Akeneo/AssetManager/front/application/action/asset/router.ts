import {redirectToRoute} from 'akeneoassetmanager/application/event/router';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/identifier';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';

export const redirectToAsset = (assetFamilyIdentifier: AssetFamilyIdentifier, assetCode: AssetCode) => {
  return redirectToRoute('akeneo_asset_manager_asset_edit', {
    assetCode: assetCode.stringValue(),
    assetFamilyIdentifier: assetFamilyIdentifier.stringValue(),
    tab: 'enrich',
  });
};

export const redirectToAssetIndex = (assetFamilyIdentifier: AssetFamilyIdentifier) => {
  return redirectToRoute('akeneo_asset_manager_asset_family_edit', {
    identifier: assetFamilyIdentifier.stringValue(),
    tab: 'asset',
  });
};
