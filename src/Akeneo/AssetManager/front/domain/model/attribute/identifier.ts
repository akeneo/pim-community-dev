import AttributeIdentifier, {
  denormalizeIdentifier,
  identifiersAreEqual,
  identifierStringValue,
  isIdentifier,
} from 'akeneoassetmanager/domain/model/identifier';

export const denormalizeAttributeIdentifier = denormalizeIdentifier;
export const attributeidentifiersAreEqual = identifiersAreEqual;
export const attributeIdentifierStringValue = identifierStringValue;
export const isAttributeIdentifier = isIdentifier;

export default AttributeIdentifier;
