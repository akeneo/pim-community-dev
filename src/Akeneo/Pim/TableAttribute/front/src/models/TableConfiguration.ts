import {LabelCollection} from '@akeneo-pim-community/shared';

export type ColumnType = 'text' | 'number' | 'boolean' | 'select';
export type ColumnCode = string;

const DATA_TYPES: ColumnType[] = ['text', 'number', 'boolean', 'select'];
const FIRST_COLUMN_DATA_TYPES: ColumnType[] = ['select'];

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

export {DATA_TYPES, FIRST_COLUMN_DATA_TYPES};
