import {LabelCollection, Locale} from '@akeneo-pim-community/shared';

type AttributeCode = string;

type AttributeType =
  | 'pim_catalog_boolean'
  | 'pim_catalog_date'
  | 'pim_catalog_file'
  | 'pim_catalog_identifier'
  | 'pim_catalog_image'
  | 'pim_catalog_metric'
  | 'pim_catalog_number'
  | 'pim_catalog_multiselect'
  | 'pim_catalog_simpleselect'
  | 'pim_catalog_price_collection'
  | 'pim_catalog_textarea'
  | 'pim_catalog_text'
  | 'pim_reference_data_multiselect'
  | 'pim_reference_data_simpleselect'
  | 'akeneo_reference_entity'
  | 'akeneo_reference_entity_collection'
  | 'pim_catalog_asset_collection'
  | 'pim_assets_collection'
  | 'pim_catalog_table';

type AttributeGroup = string;

type AttributeFieldType =
  | 'akeneo-switch-field'
  | 'akeneo-datepicker-field'
  | 'akeneo-media-uploader-field'
  | 'akeneo-image-uploader-field'
  | 'akeneo-metric-field'
  | 'akeneo-multi-select-field'
  | 'akeneo-number-field'
  | 'akeneo-price-collection-field'
  | 'akeneo-simple-select-field'
  | 'akeneo-text-field'
  | 'akeneo-textarea-field';

/**
 * This value contains the name of the module to load to filter this attribute type for each feature.
 * Example:
 * {
 *   'product-export-builder': 'akeneo-attribute-identifier-filter',
 * }
 */
type AttributeFilterTypes = {
  [feature: string]: string;
};

type Version<T> = {
  id: number;
  author: string;
  resource_id: string;
  snapshot: T;
  changeset: {
    [key: string]: {old: any, new: any};
  },
  context: null,
  version: number,
  logged_at: string;
  pending: boolean;
};

/**
 * This type contains all the information given by the backend. Several of these properties are annotated as "null"
 * because they are not used for now. Don't hesitate to update this file once you need this property.
 */
export type Attribute = {
  scopable: boolean;
  localizable: boolean;
  code: AttributeCode;
  type: AttributeType;
  group: AttributeGroup;
  unique: boolean;
  useable_as_grid_filter: boolean;
  allowed_extensions: string[];
  metric_family: null;
  default_metric_unit: null;
  reference_data_name?: string;
  available_locales: Locale[];
  max_characters: null;
  validation_rule: null;
  validation_regexp: null;
  wysiwyg_enabled: null;
  number_min: null;
  number_max: null;
  decimals_allowed: null;
  negative_allowed: null;
  date_min: null;
  date_max: null;
  max_file_size: null;
  minimum_input_length: null;
  sort_order: number;
  labels: LabelCollection;
  guidelines: any[];
  auto_option_sorting: null;
  default_value: null;
  empty_value: null;
  field_type: AttributeFieldType;
  filter_types: AttributeFilterTypes;
  is_locale_specific: boolean;
  is_main_identifier: boolean;
  is_read_only: undefined|boolean;
  meta: {
    id: number;
    structure_version: number;
    model_type: 'attribute';
    created: Version<Attribute>,
    updated: Version<Attribute>,
  }
};
