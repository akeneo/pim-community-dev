import AssetFamilyIdentifier, {
  assetFamilyidentifiersAreEqual,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {isString} from 'akeneoassetmanager/domain/model/utils';

export type AssetType = AssetFamilyIdentifier | null;

export const isValidAssetType = (assetType: any): assetType is string => {
  return isString(assetType);
};

export const createAssetTypeFromNormalized = (normalizedAssetType: any) => {
  if (!(isValidAssetType(normalizedAssetType) || null === normalizedAssetType)) {
    throw new Error(`AssetType should be a valid string or null`);
  }

  return normalizedAssetType;
};

export const assetTypeAreEqual = (first: AssetType, second: AssetType): boolean => {
  return (
    (null === first && null === second) ||
    (null !== first && null !== second && assetFamilyidentifiersAreEqual(first, second))
  );
};

export const assetTypeStringValue = (assetType: AssetType) => (null === assetType ? '' : assetType);
export const assetTypeIsEmpty = (assetType: AssetType): assetType is null => assetType === null;
