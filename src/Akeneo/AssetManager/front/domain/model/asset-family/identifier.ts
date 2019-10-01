import AssetFamilyIdentifier, {
  denormalizeIdentifier,
  identifiersAreEqual,
  identifierStringValue,
  isIdentifier,
} from 'akeneoassetmanager/domain/model/identifier';

export const denormalizeAssetFamilyIdentifier = denormalizeIdentifier;
export const assetFamilyidentifiersAreEqual = identifiersAreEqual;
export const assetFamilyIdentifierStringValue = identifierStringValue;
export const isAssetFamilyIdentifier = isIdentifier;

export default AssetFamilyIdentifier;
