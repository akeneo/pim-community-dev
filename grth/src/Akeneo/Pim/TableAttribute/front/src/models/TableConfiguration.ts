import {LabelCollection} from '@akeneo-pim-community/shared';
import {ReferenceEntityIdentifierOrCode} from './ReferenceEntity';

export type DataType = 'text' | 'number' | 'boolean' | 'select' | 'record';
export type ColumnCode = string;

export type TextColumnValidation = {
  max_length?: number;
};

export type NumberColumnValidation = {
  min?: number;
  max?: number;
  decimals_allowed?: boolean;
};

type BooleanColumnValidation = {};

type SelectColumnValidation = {};

type RecordColumnValidation = {};

export type ColumnValidation =
  | TextColumnValidation
  | NumberColumnValidation
  | BooleanColumnValidation
  | RecordColumnValidation;

export type SelectOptionCode = string;

export type SelectOption = {
  code: SelectOptionCode;
  labels: LabelCollection;
};

type RecordOptionCode = string;

export type RecordOption = {
  code: RecordOptionCode;
};

export type TextColumnDefinition = {
  code: ColumnCode;
  labels: LabelCollection;
  data_type: 'text';
  validations: TextColumnValidation;
  is_required_for_completeness?: boolean;
};

export type NumberColumnDefinition = {
  code: ColumnCode;
  labels: LabelCollection;
  data_type: 'number';
  validations: NumberColumnValidation;
  is_required_for_completeness?: boolean;
};

export type BooleanColumnDefinition = {
  code: ColumnCode;
  labels: LabelCollection;
  data_type: 'boolean';
  validations: BooleanColumnValidation;
  is_required_for_completeness?: boolean;
};

export type SelectColumnDefinition = {
  code: ColumnCode;
  labels: LabelCollection;
  data_type: 'select';
  validations: SelectColumnValidation;
  is_required_for_completeness?: boolean;
  options?: SelectOption[];
};

export type RecordColumnDefinition = {
  code: ColumnCode;
  labels: LabelCollection;
  data_type: 'record';
  validations: RecordColumnValidation;
  is_required_for_completeness?: boolean;
  reference_entity_identifier: ReferenceEntityIdentifierOrCode;
};

export type ColumnDefinition =
  | TextColumnDefinition
  | NumberColumnDefinition
  | BooleanColumnDefinition
  | SelectColumnDefinition
  | RecordColumnDefinition;

export type TableConfiguration = ColumnDefinition[];

export const isColumnCodeNotAvailable: (columnCode: ColumnCode) => boolean = columnCode =>
  ['product', 'product_model', 'attribute'].includes(columnCode.toLowerCase());
