import {LabelCollection} from '@akeneo-pim-community/shared';

export type ColumnType = 'text' | 'number' | 'boolean' | 'select';
export type ColumnCode = string;

const DATA_TYPES: ColumnType[] = ['text', 'number', 'boolean', 'select'];

type TextColumnValidation = {
  max_length?: number;
};

type NumberColumnValidation = {
  min?: number;
  max?: number;
  decimals_allowed?: boolean;
};

type BooleanColumnValidation = {};

type SelectColumnValidation = {};

export type ColumnValidation = TextColumnValidation | NumberColumnValidation | BooleanColumnValidation;

export type ColumnDefinition = {
  code: ColumnCode;
  labels: LabelCollection;
} & (
  | {
      data_type: 'text';
      validations: TextColumnValidation;
    }
  | {
      data_type: 'number';
      validations: NumberColumnValidation;
    }
  | {
      data_type: 'boolean';
      validations: BooleanColumnValidation;
    }
  | {
      data_type: 'select';
      validations: SelectColumnValidation;
    }
);

export type TableConfiguration = ColumnDefinition[];

export {DATA_TYPES};
