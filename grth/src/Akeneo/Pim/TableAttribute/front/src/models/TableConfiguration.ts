import {LabelCollection} from '@akeneo-pim-community/shared';

export type DataType = 'text' | 'number' | 'boolean' | 'select';
export type ColumnCode = string;

export type TextColumnValidation = {
  max_length?: number;
  required_for_completeness?: boolean;
};

export type NumberColumnValidation = {
  min?: number;
  max?: number;
  decimals_allowed?: boolean;
  required_for_completeness?: boolean;
};

type BooleanColumnValidation = {
  required_for_completeness?: boolean;
};

type SelectColumnValidation = {
  required_for_completeness?: boolean;
};

export type ColumnValidation = TextColumnValidation | NumberColumnValidation | BooleanColumnValidation;

export type SelectOptionCode = string;

export type SelectOption = {
  code: SelectOptionCode;
  labels: LabelCollection;
};

export type TextColumnDefinition = {
  code: ColumnCode;
  labels: LabelCollection;
  data_type: 'text';
  validations: TextColumnValidation;
};

export type NumberColumnDefinition = {
  code: ColumnCode;
  labels: LabelCollection;
  data_type: 'number';
  validations: NumberColumnValidation;
};

export type BooleanColumnDefinition = {
  code: ColumnCode;
  labels: LabelCollection;
  data_type: 'boolean';
  validations: BooleanColumnValidation;
};

export type SelectColumnDefinition = {
  code: ColumnCode;
  labels: LabelCollection;
  data_type: 'select';
  validations: SelectColumnValidation;
  options?: SelectOption[];
};

export type ColumnDefinition =
  | TextColumnDefinition
  | NumberColumnDefinition
  | BooleanColumnDefinition
  | SelectColumnDefinition;

export type TableConfiguration = ColumnDefinition[];

export const isColumnCodeNotAvailable: (columnCode: ColumnCode) => boolean = columnCode =>
  ['product', 'product_model', 'attribute'].includes(columnCode.toLowerCase());
