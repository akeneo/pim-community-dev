import {LabelCollection} from '@akeneo-pim-community/shared';

export type ColumnType = 'text' | 'number' | 'boolean' | 'select';
export type ColumnCode = string;

const DATA_TYPES: ColumnType[] = ['text', 'number', 'boolean', 'select'];

type TextColumnValidation = {
  max_length?: number;
};

type NumberColumnValidation = {};

type BooleanColumnValidation = {};

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
);

export type TableConfiguration = ColumnDefinition[];

export {DATA_TYPES};
