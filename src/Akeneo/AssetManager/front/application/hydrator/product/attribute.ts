import {ProductAttribute} from 'akeneoassetmanager/domain/model/product/attribute';
import {validateKeys} from 'akeneoassetmanager/application/hydrator/hydrator';

export const hydrator = (normalizedAttribute: any): ProductAttribute => {
  const expectedKeys = ['code', 'type', 'labels', 'reference_data_name', 'useable_as_grid_filter'];
  validateKeys(normalizedAttribute, expectedKeys, 'The provided raw attribute seems to be malformed.');

  return normalizedAttribute;
};

export default hydrator;
