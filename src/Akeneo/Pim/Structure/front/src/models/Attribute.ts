import {LabelCollection, Locale} from '@akeneo-pim-community/shared';

export type AttributeCode = string;

export enum ATTRIBUTE_TYPE {
  BOOLEAN = 'pim_catalog_boolean',
  DATE = 'pim_catalog_date',
  FILE = 'pim_catalog_file',
  IDENTIFIER = 'pim_catalog_identifier',
  IMAGE = 'pim_catalog_image',
  METRIC = 'pim_catalog_metric',
  NUMBER = 'pim_catalog_number',
  OPTION_MULTI_SELECT = 'pim_catalog_multiselect',
  OPTION_SIMPLE_SELECT = 'pim_catalog_simpleselect',
  PRICE_COLLECTION = 'pim_catalog_price_collection',
  TEXTAREA = 'pim_catalog_textarea',
  TEXT = 'pim_catalog_text',
  REFERENCE_DATA_MULTI_SELECT = 'pim_reference_data_multiselect',
  REFERENCE_DATA_SIMPLE_SELECT = 'pim_reference_data_simpleselect',
  REFERENCE_ENTITY_SIMPLE_SELECT = 'akeneo_reference_entity',
  REFERENCE_ENTITY_COLLECTION = 'akeneo_reference_entity_collection',
  ASSET_COLLECTION = 'pim_catalog_asset_collection',
  LEGACY_ASSET_COLLECTION = 'pim_assets_collection',
  TABLE = 'pim_catalog_table',
}

export type AttributeType =
  | ATTRIBUTE_TYPE.BOOLEAN
  | ATTRIBUTE_TYPE.DATE
  | ATTRIBUTE_TYPE.FILE
  | ATTRIBUTE_TYPE.IDENTIFIER
  | ATTRIBUTE_TYPE.IMAGE
  | ATTRIBUTE_TYPE.METRIC
  | ATTRIBUTE_TYPE.NUMBER
  | ATTRIBUTE_TYPE.OPTION_MULTI_SELECT
  | ATTRIBUTE_TYPE.OPTION_SIMPLE_SELECT
  | ATTRIBUTE_TYPE.PRICE_COLLECTION
  | ATTRIBUTE_TYPE.TEXTAREA
  | ATTRIBUTE_TYPE.TEXT
  | ATTRIBUTE_TYPE.REFERENCE_DATA_MULTI_SELECT
  | ATTRIBUTE_TYPE.REFERENCE_DATA_SIMPLE_SELECT
  | ATTRIBUTE_TYPE.REFERENCE_ENTITY_COLLECTION
  | ATTRIBUTE_TYPE.REFERENCE_ENTITY_SIMPLE_SELECT
  | ATTRIBUTE_TYPE.ASSET_COLLECTION
  | ATTRIBUTE_TYPE.LEGACY_ASSET_COLLECTION
  | ATTRIBUTE_TYPE.TABLE;

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
    [key: string]: {old: any; new: any};
  };
  context: null;
  version: number;
  logged_at: string;
  pending: boolean;
};

/**
 * This type contains all the information given by the backend. Several of these properties have dummy type because
 * they are not used for now. Don't hesitate to update this file once you need this property.
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
  is_read_only: undefined | boolean;
  meta: {
    id: number;
    structure_version: number;
    model_type: 'attribute';
    created: Version<Attribute>;
    updated: Version<Attribute>;
  };
};
