import {Attribute} from 'akeneoassetmanager/platform/model/structure/attribute';
import {isString, isLabels} from 'akeneoassetmanager/domain/model/utils';
import {postJSON} from 'akeneoassetmanager/tools/fetch';
const routing = require('routing');

const ASSET_COLLECTION_ATTRIBUTE_LIMIT = 100;

export const fetchAssetAttributes = async (): Promise<Attribute[]> => {
  const attributes = await postJSON(
    routing.generate('pim_enrich_attribute_rest_index', {
      types: ['pim_catalog_asset_collection'],
      options: {limit: ASSET_COLLECTION_ATTRIBUTE_LIMIT},
    }),
    {}
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

  if (!Array.isArray(normalizedAttribute.available_locales)) {
    throw Error('The available_locales is not well formated');
  }

  const {is_read_only = null, reference_data_name = null, available_locales = null, ...attribute} = {
    ...normalizedAttribute,
    isReadOnly: normalizedAttribute.is_read_only,
    referenceDataName: normalizedAttribute.reference_data_name,
    availableLocales: normalizedAttribute.available_locales,
  };

  return attribute;
};
