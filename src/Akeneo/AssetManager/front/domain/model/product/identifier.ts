import ProductIdentifier, {
  denormalizeIdentifier,
  identifiersAreEqual,
  identifierStringValue,
  isIdentifier,
} from 'akeneoassetmanager/domain/model/identifier';

export const denormalizeProductIdentifier = denormalizeIdentifier;
export const productidentifiersAreEqual = identifiersAreEqual;
export const productIdentifierStringValue = identifierStringValue;
export const isProductIdentifier = isIdentifier;

export default ProductIdentifier;
