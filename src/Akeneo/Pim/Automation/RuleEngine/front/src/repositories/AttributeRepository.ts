import { Attribute } from '../models/Attribute';
import { Router } from '../dependenciesTools';
import {
  fetchAttributeByIdentifier,
  fetchAttributesByIdentifiers,
} from '../fetch/AttributeFetcher';

const cacheAttributes: { [identifier: string]: Attribute | null } = {};

export const getAttributeByIdentifier = async (
  attributeIdentifier: string,
  router: Router
): Promise<Attribute | null> => {
  if (!cacheAttributes[attributeIdentifier]) {
    cacheAttributes[attributeIdentifier] = await fetchAttributeByIdentifier(
      attributeIdentifier,
      router
    );
  }

  return cacheAttributes[attributeIdentifier];
};

export const getAttributesByIdentifiers = async (
  attributeIdentifiers: string[],
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
    return { ...previousValue, currentValue: cacheAttributes[currentValue] };
  }, {});
};
