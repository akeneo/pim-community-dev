import {transformVolumesToAxis} from './catalogVolumeWrapper';
import {GetCatalogVolumeInterface} from '../hooks/useCatalogVolumeByAxis';

const volumes = {
  count_attributes: {
    value: 112,
    has_warning: false,
    type: 'count',
  },
  count_categories: {
    value: 289,
    has_warning: false,
    type: 'count',
  },
  count_category_trees: {
    value: 6,
    has_warning: true,
    type: 'count',
  },
  count_channels: {
    value: 5,
    has_warning: true,
    type: 'count',
  },
  count_families: {
    value: 108,
    has_warning: false,
    type: 'count',
  },
  count_locales: {
    value: 4,
    has_warning: false,
    type: 'count',
  },
  count_localizable_and_scopable_attributes: {
    value: 5,
    has_warning: false,
    type: 'count',
  },
  count_localizable_attributes: {
    value: 13,
    has_warning: false,
    type: 'count',
  },
  count_scopable_attributes: {
    value: 3,
    has_warning: false,
    type: 'count',
  },
  count_products: {
    value: 1389,
    has_warning: false,
    type: 'count',
  },
  count_product_models: {
    value: 100,
    has_warning: false,
    type: 'count',
  },
  count_variant_products: {
    value: 261,
    has_warning: false,
    type: 'count',
  },
  count_product_and_product_model_values: {
    value: 9200,
    has_warning: false,
    type: 'count',
  },
  count_reference_entity: {
    value: 7,
    has_warning: false,
    type: 'count',
  },
  count_asset_family: {
    value: 8,
    has_warning: false,
    type: 'count',
  },
  average_max_attributes_per_family: {
    value: {
      average: 4,
      max: 43,
    },
    has_warning: false,
    type: 'average_max',
  },
  average_max_options_per_attribute: {
    value: {
      average: 69,
      max: 1007,
    },
    has_warning: true,
    type: 'average_max',
  },
  average_max_product_and_product_model_values: {
    value: {
      average: 7,
      max: 22,
    },
    has_warning: false,
    type: 'average_max',
  },
  average_max_records_per_reference_entity: {
    value: {
      average: 1432,
      max: 10002,
    },
    has_warning: false,
    type: 'average_max',
  },
  average_max_attributes_per_reference_entity: {
    value: {
      average: 6,
      max: 9,
    },
    has_warning: false,
    type: 'average_max',
  },
  average_max_number_of_values_per_record: {
    value: {
      average: 6,
      max: 9,
    },
    has_warning: false,
    type: 'average_max',
  },
  average_max_number_of_values_per_asset: {
    value: {
      average: 3,
      max: 4,
    },
    has_warning: false,
    type: 'average_max',
  },
  average_max_assets_per_asset_family: {
    value: {
      average: 7,
      max: 21,
    },
    has_warning: false,
    type: 'average_max',
  },
  average_max_attributes_per_asset_family: {
    value: {
      average: 4,
      max: 5,
    },
    has_warning: false,
    type: 'average_max',
  },
};

const getMockCatalogVolume: GetCatalogVolumeInterface = async () => {
  return transformVolumesToAxis(volumes);
};

export {getMockCatalogVolume};
