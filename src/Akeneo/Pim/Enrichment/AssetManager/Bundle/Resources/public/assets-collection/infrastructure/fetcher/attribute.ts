import promisify from 'akeneoassetmanager/tools/promisify';
import {Attribute} from 'akeneopimenrichmentassetmanager/platform/model/structure/attribute';
import {validateLabels} from 'akeneopimenrichmentassetmanager/assets-collection/domain/model/asset';
const fetcherRegistry = require('pim/fetcher-registry');

export const fetchAssetAttributes = async (): Promise<Attribute[]> => {
  const attributes = await promisify(
    fetcherRegistry.getFetcher('attribute').fetchByTypes(['akeneo_asset_multiple_link'])
  );

  return denormalizeAssetAttributeCollection(attributes);
};

const denormalizeAssetAttributeCollection = (attributes: any): Attribute[] => {
  if (!Array.isArray(attributes)) {
    throw Error('not a valid attribute collection');
  }

  return attributes.map((attribute: any) => denormalizeAssetAttribute(attribute));
};

const denormalizeAssetAttribute = (normalizedAttribute: any): Attribute => {
  if (undefined === normalizedAttribute.code || typeof normalizedAttribute.code !== 'string') {
    throw Error('The code is not well formated');
  }

  if (undefined === normalizedAttribute.labels || !validateLabels(normalizedAttribute.labels)) {
    throw Error('The label collection is not well formated');
  }

  if (undefined === normalizedAttribute.group || typeof normalizedAttribute.group !== 'string') {
    throw Error('The group is not well formated');
  }

  if (
    normalizedAttribute.is_read_only !== null &&
    (undefined === normalizedAttribute.is_read_only || typeof normalizedAttribute.is_read_only !== 'boolean')
  ) {
    throw Error('The is_read_only is not well formated');
  }

  if (
    undefined === normalizedAttribute.reference_data_name ||
    typeof normalizedAttribute.reference_data_name !== 'string'
  ) {
    throw Error('The reference_data_name is not well formated');
  }
  const {is_read_only = null, reference_data_name = null, ...attribute} = {
    ...normalizedAttribute,
    isReadOnly: normalizedAttribute.is_read_only,
    referenceDataName: normalizedAttribute.reference_data_name,
  };

  return attribute;
};
