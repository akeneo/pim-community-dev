import {TableAttribute} from '../../src';
import {getComplexTableConfiguration} from './TableConfiguration';

const getComplexTableAttribute: (firstColumnType?: 'select' | 'reference_entity') => TableAttribute = (
  firstColumnType = 'select'
) => {
  return {
    code: 'nutrition',
    labels: {
      en_US: 'Nutrition',
    },
    allowed_extensions: [],
    auto_option_sorting: null,
    available_locales: [],
    date_max: null,
    date_min: null,
    decimals_allowed: null,
    default_metric_unit: null,
    empty_value: null,
    field_type: 'akeneo-table-field',
    filter_types: {},
    group: 'marketing',
    is_locale_specific: false,
    is_read_only: false,
    localizable: false,
    max_characters: null,
    max_file_size: null,
    meta: {
      id: 42,
    },
    metric_family: null,
    minimum_input_length: null,
    negative_allowed: null,
    number_max: null,
    number_min: null,
    reference_data_name: null,
    type: 'pim_catalog_table',
    scopable: false,
    sort_order: 0,
    unique: false,
    useable_as_grid_filter: false,
    validation_regexp: null,
    validation_rule: null,
    wysiwyg_enabled: null,
    table_configuration: getComplexTableConfiguration(firstColumnType),
  };
};

const getTableAttributeWithoutColumn: () => TableAttribute = () => {
  return {
    code: 'nutrition',
    labels: {
      en_US: 'Nutrition',
    },
    allowed_extensions: [],
    auto_option_sorting: null,
    available_locales: [],
    date_max: null,
    date_min: null,
    decimals_allowed: null,
    default_metric_unit: null,
    empty_value: null,
    field_type: 'akeneo-table-field',
    filter_types: {},
    group: 'marketing',
    is_locale_specific: false,
    is_read_only: false,
    localizable: false,
    max_characters: null,
    max_file_size: null,
    meta: {
      id: 42,
    },
    metric_family: null,
    minimum_input_length: null,
    negative_allowed: null,
    number_max: null,
    number_min: null,
    reference_data_name: null,
    type: 'pim_catalog_table',
    scopable: false,
    sort_order: 0,
    unique: false,
    useable_as_grid_filter: false,
    validation_regexp: null,
    validation_rule: null,
    wysiwyg_enabled: null,
    table_configuration: [],
  };
};

export {getComplexTableAttribute, getTableAttributeWithoutColumn};
