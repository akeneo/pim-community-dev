import {LabelCollection} from '@akeneo-pim-community/shared';

type ProductAttributeCode = string;

type ProductAttribute = {
  code: ProductAttributeCode;
  type: string;
  labels: LabelCollection;
  reference_data_name: string;
  useable_as_grid_filter: boolean;
};

export {ProductAttribute, ProductAttributeCode};
