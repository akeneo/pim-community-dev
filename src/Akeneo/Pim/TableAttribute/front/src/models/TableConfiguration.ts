import {LabelCollection} from '@akeneo-pim-community/shared';

export type ColumnType = 'text' | 'number' | 'boolean' | 'select';
export type ColumnCode = string;

const DATA_TYPES: ColumnType[] = ['text', 'number', 'boolean', 'select'];

export type ColumnDefinition = {
  code: ColumnCode;
  data_type: ColumnType;
  labels: LabelCollection;
};

export type TableConfiguration = ColumnDefinition[];

export {DATA_TYPES};
