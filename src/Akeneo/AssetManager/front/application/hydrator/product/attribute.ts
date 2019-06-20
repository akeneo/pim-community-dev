import Attribute, {NormalizedAttribute, denormalizeAttribute} from 'akeneoassetmanager/domain/model/product/attribute';
import {validateKeys} from 'akeneoassetmanager/application/hydrator/hydrator';

export const hydrator = (denormalize: (denormalizeAttribute: NormalizedAttribute) => Attribute) => (
  normalizedAttribute: any
): Attribute => {
  const expectedKeys = ['code', 'type', 'labels', 'reference_data_name', 'useable_as_grid_filter'];
  validateKeys(normalizedAttribute, expectedKeys, 'The provided raw attribute seems to be malformed.');

  return denormalize(normalizedAttribute);
};

const hydrateAttribute = hydrator(denormalizeAttribute);

export default hydrateAttribute;
