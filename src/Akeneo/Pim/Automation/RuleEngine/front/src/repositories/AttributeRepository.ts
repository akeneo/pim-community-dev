import { Attribute, AttributeCode } from '../models';
import { Router } from '../dependenciesTools';
import {
  fetchAttributeByIdentifier,
  fetchAttributesByIdentifiers,
} from '../fetch/AttributeFetcher';

const cacheAttributes: { [identifier: string]: Attribute | null } = {};

export const clearAttributeRepositoryCache = () => {
  for (const key in cacheAttributes) {
    delete cacheAttributes[key];
  }
};

export const getAttributeByIdentifier = async (
  attributeIdentifier: AttributeCode,
  router: Router
): Promise<Attribute | null> => {
  if (
    !Object.prototype.hasOwnProperty.call(cacheAttributes, attributeIdentifier)
  ) {
    cacheAttributes[attributeIdentifier] = await fetchAttributeByIdentifier(
      attributeIdentifier,
      router
    );
  }

  return cacheAttributes[attributeIdentifier];
};

export const getAttributesByIdentifiers = async (
  attributeIdentifiers: AttributeCode[],
  router: Router
): Promise<{ [identifier: string]: Attribute | null }> => {
  const attributeIdentifiersToGet = attributeIdentifiers.filter(
    attributeIndentifier => {
      return !Object.keys(cacheAttributes).includes(attributeIndentifier);
    }
  );

  if (attributeIdentifiersToGet.length) {
    const attributes = await fetchAttributesByIdentifiers(
      attributeIdentifiersToGet,
      router
    );
    attributeIdentifiersToGet.forEach(attributeIdentifier => {
      const matchingAttribute = attributes.find((attribute: Attribute) => {
        return attribute.code === attributeIdentifier;
      });
      cacheAttributes[attributeIdentifier] = matchingAttribute || null;
    });
  }

  return attributeIdentifiers.reduce((previousValue, currentValue) => {
    const result: { [identifier: string]: Attribute | null } = {
      ...previousValue,
    };
    result[currentValue] = cacheAttributes[currentValue];
    return result;
  }, {});
};
