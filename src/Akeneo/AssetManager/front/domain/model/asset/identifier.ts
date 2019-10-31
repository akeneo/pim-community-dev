import AssetIdentifier, {
  denormalizeIdentifier,
  identifiersAreEqual,
  identifierStringValue,
  isIdentifier,
} from 'akeneoassetmanager/domain/model/identifier';

export const denormalizeAssetIdentifier = denormalizeIdentifier;
export const assetidentifiersAreEqual = identifiersAreEqual;
export const assetIdentifierStringValue = identifierStringValue;
export const isAssetIdentifier = isIdentifier;

export default AssetIdentifier;
