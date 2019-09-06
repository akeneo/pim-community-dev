import {isString} from 'util';
import promisify from 'akeneoassetmanager/tools/promisify';
import {Attribute} from 'akeneopimenrichmentassetmanager/platform/model/structure/attribute';
import {isLabels} from 'akeneopimenrichmentassetmanager/assets-collection/domain/model/asset';
const fetcherRegistry = require('pim/fetcher-registry');

/**
 * Need to export this function in a variable to be able to mock it in our tests.
 * We couldn't require the pim/fetcher-registry in our test stack. We need to mock the legacy fetcher used.
 */
export const attributeFetcher = () => fetcherRegistry.getFetcher('attribute');
export const fetchAssetAttributes = (attributeFetcher: any) => async (): Promise<Attribute[]> => {
  const attributes = await promisify(attributeFetcher.fetchByTypes(['pim_catalog_asset_collection']));

  return denormalizeAssetAttributeCollection(attributes);
};

const denormalizeAssetAttributeCollection = (attributes: any): Attribute[] => {
  if (!Array.isArray(attributes)) {
    throw Error('not a valid attribute collection');
  }

  return attributes.map((attribute: any) => denormalizeAssetAttribute(attribute));
};

const denormalizeAssetAttribute = (normalizedAttribute: any): Attribute => {
  if (!isString(normalizedAttribute.code)) {
    throw Error('The code is not well formated');
  }

  if (!isLabels(normalizedAttribute.labels)) {
    throw Error('The label collection is not well formated');
  }

  if (!isString(normalizedAttribute.group)) {
    throw Error('The group is not well formated');
  }

  if (
    null !== normalizedAttribute.is_read_only &&
    (undefined === normalizedAttribute.is_read_only || typeof normalizedAttribute.is_read_only !== 'boolean')
  ) {
    throw Error('The is_read_only is not well formated');
  }

  if (!isString(normalizedAttribute.reference_data_name)) {
    throw Error('The reference_data_name is not well formated');
  }

  const {is_read_only = null, reference_data_name = null, ...attribute} = {
    ...normalizedAttribute,
    isReadOnly: normalizedAttribute.is_read_only,
    referenceDataName: normalizedAttribute.reference_data_name,
  };

  return attribute;
};
