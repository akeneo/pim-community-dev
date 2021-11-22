import {transformVolumesToKeyFigures} from './catalogVolumeWrapper';

const volumes = {
  count_attributes: {value: 77, has_warning: false, type: 'count'},
  count_categories: {value: 168, has_warning: false, type: 'count'},
  count_category_trees: {value: 4, has_warning: false, type: 'count'},
  count_channels: {value: 3, has_warning: false, type: 'count'},
  count_families: {value: 17, has_warning: false, type: 'count'},
  count_locales: {value: 3, has_warning: false, type: 'count'},
  count_localizable_and_scopable_attributes: {value: 2, has_warning: false, type: 'count'},
  count_localizable_attributes: {value: 10, has_warning: false, type: 'count'},
  count_scopable_attributes: {value: 1, has_warning: false, type: 'count'},
  count_products: {value: 1239, has_warning: false, type: 'count'},
  count_product_models: {value: 81, has_warning: false, type: 'count'},
  count_variant_products: {value: 240, has_warning: false, type: 'count'},
  count_product_and_product_model_values: {value: 0, has_warning: false, type: 'count'},
  average_max_attributes_per_family: {
    value: {average: 14, max: 33},
    has_warning: false,
    type: 'average_max',
  },
  average_max_options_per_attribute: {
    value: {average: 0, max: 0},
    has_warning: false,
    type: 'average_max',
  },
  average_max_product_and_product_model_values: {
    value: {average: 0, max: 0},
    has_warning: false,
    type: 'average_max',
  },
};

const getMockCatalogVolume = () => {
  return transformVolumesToKeyFigures(volumes);
};

export {getMockCatalogVolume};
