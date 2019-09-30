import {redirectToRoute} from 'akeneoassetmanager/application/event/router';
import AssetFamilyIdentifier, {
  assetFamilyIdentifierStringValue,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import AssetCode, {assetCodeStringValue} from 'akeneoassetmanager/domain/model/asset/code';

export const redirectToAsset = (assetFamilyIdentifier: AssetFamilyIdentifier, assetCode: AssetCode) => {
  return redirectToRoute('akeneo_asset_manager_asset_edit', {
    assetCode: assetCodeStringValue(assetCode),
    assetFamilyIdentifier: assetFamilyIdentifierStringValue(assetFamilyIdentifier),
    tab: 'enrich',
  });
};

export const redirectToAssetIndex = (assetFamilyIdentifier: AssetFamilyIdentifier) => {
  return redirectToRoute('akeneo_asset_manager_asset_family_edit', {
    identifier: assetFamilyIdentifierStringValue(assetFamilyIdentifier),
    tab: 'asset',
  });
};
