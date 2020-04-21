import {Router} from "../dependenciesTools";
import {httpGet} from "./fetch";
import {Attribute} from "../models/Attribute";

let cacheAttributes: {[identifier: string]: (Attribute | null)} = {};

export const getAttributeByIdentifier = async (attributeIdentifier: string, router: Router): Promise<Attribute | null> => {
  if (!cacheAttributes[attributeIdentifier]) {
    const url = router.generate('pim_enrich_attribute_rest_get', {identifier: attributeIdentifier});
    const response = await httpGet(url);
    cacheAttributes[attributeIdentifier] = response.status === 404 ? null : await response.json();
  }

  return cacheAttributes[attributeIdentifier];
};

export const getAttributesByIdentifiers = async(attributeIdentifiers: string[], router: Router): Promise<{[identifier: string]: (Attribute | null)}> => {
  const attributeIdentifiersToGet = attributeIdentifiers.filter((attributeIndentifier) => {
    return !Object.keys(cacheAttributes).includes(attributeIndentifier);
  });

  if (attributeIdentifiersToGet.length) {
    const url = router.generate('pim_enrich_attribute_rest_index', {identifiers: attributeIdentifiersToGet.join(',')});
    const response = await httpGet(url);
    const json = await response.json();
    attributeIdentifiersToGet.forEach((attributeIdentifier) => {
      const matchingAttribute = json.find((attribute: Attribute) => {
        return attribute.code === attributeIdentifier;
      });
      cacheAttributes[attributeIdentifier] = matchingAttribute || null;
    });
  }

  let result: {[identifier: string]: (Attribute | null)} = {};
  attributeIdentifiers.forEach((attributeIdentifier) => {
    result[attributeIdentifier] = cacheAttributes[attributeIdentifier];
  });

  return result;
}
