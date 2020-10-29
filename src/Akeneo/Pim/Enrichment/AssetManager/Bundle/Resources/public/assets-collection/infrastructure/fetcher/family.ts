import promisify from 'akeneoassetmanager/tools/promisify';
import {Family, FamilyCode, AttributeRequirements} from 'akeneoassetmanager/platform/model/structure/family';
import {isString, isObject, isLabels} from 'akeneoassetmanager/domain/model/utils';
const fetcherRegistry = require('pim/fetcher-registry');

/**
 * Need to export this function in a variable to be able to mock it in our tests.
 * We couldn't require the pim/fetcher-registry in our test stack. We need to mock the legacy fetcher used.
 */
export const familyFetcher = () => fetcherRegistry.getFetcher('family');
export const fetchFamily = (familyFetcher: any) => async (familyCode: FamilyCode): Promise<Family> => {
  const family = await promisify(familyFetcher.fetch(familyCode));

  return denormalizeFamily(family);
};

const denormalizeFamily = (normalizedFamily: any): Family => {
  if (!isString(normalizedFamily.code)) {
    throw Error('The code is not well formated');
  }

  if (!isLabels(normalizedFamily.labels)) {
    throw Error('The label collection is not well formated');
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

  return !Object.keys(attributeRequirements).some((key: string): boolean => {
    return !isString(key) || attributeRequirements[key].some((attributeCode: any): boolean => !isString(attributeCode));
  });
};
