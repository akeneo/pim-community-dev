import {LabelCollection, Locale} from '@akeneo-pim-community/shared';
import {AttributeType} from './AttributeType';
import {Version} from './Version';

export type AttributeCode = string;

export type AttributeId = number;

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
  | 'akeneo-textarea-field'
  | string;

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
  reference_data_name?: string | null;
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
  guidelines: {[locale: string]: string};
  auto_option_sorting: null;
  default_value: null;
  empty_value: null;
  field_type: AttributeFieldType;
  filter_types: AttributeFilterTypes;
  is_locale_specific: boolean;
  is_main_identifier: boolean;
  is_read_only: undefined | boolean;
  meta: {
    id: AttributeId;
    structure_version?: number;
    model_type?: 'attribute';
    created?: Version<Attribute, AttributeId>;
    updated?: Version<Attribute, AttributeId>;
  };
};
