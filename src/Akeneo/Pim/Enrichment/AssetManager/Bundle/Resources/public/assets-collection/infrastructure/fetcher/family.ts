import promisify from 'akeneoassetmanager/tools/promisify';
import {Family, FamilyCode, AttributeRequirements} from 'akeneopimenrichmentassetmanager/platform/model/structure/family';
const fetcherRegistry = require('pim/fetcher-registry');
import {isString, isObject} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/fetcher/utils';

export const fetchFamily = async (familyCode: FamilyCode): Promise<Family> => {
  const family = await promisify(fetcherRegistry.getFetcher('family').fetch(familyCode));

  return denormalizeFamily(family);
};

const denormalizeFamily = (normalizedFamily: any): Family => {
  if (!isString(normalizedFamily.code)) {
    throw Error('The code is not well formated');
  }

  if (!isAttributeRequirements(normalizedFamily.attribute_requirements)) {
    throw Error('The attribute_requirements is not well formated');
  }

  const {attribute_requirements = null, ...family} = {
    ...normalizedFamily,
    attributeRequirements: normalizedFamily.attribute_requirements,
  };

  return family;
};

const isAttributeRequirements = (attributeRequirements: any): attributeRequirements is AttributeRequirements => {
  if (!isObject(attributeRequirements)) {
    return false;
  }

  return !Object.keys(attributeRequirements).some(
    (key: string): boolean => {
      return (
        !isString(key) ||
        attributeRequirements[key].some((attributeCode: any): boolean => !isString(attributeCode))
      );
    }
  );
};
