import AssetFamilyIdentifier, {
  denormalizeIdentifier,
  identifiersAreEqual,
  identifierStringValue,
  isIdentifier,
  isEmptyIdentifier,
} from 'akeneoassetmanager/domain/model/identifier';

export const denormalizeAssetFamilyIdentifier = denormalizeIdentifier;
export const assetFamilyidentifiersAreEqual = identifiersAreEqual;
export const assetFamilyIdentifierStringValue = identifierStringValue;
export const isAssetFamilyIdentifier = isIdentifier;
export const isEmptyAssetFamilyIdentifier = isEmptyIdentifier;

export default AssetFamilyIdentifier;
