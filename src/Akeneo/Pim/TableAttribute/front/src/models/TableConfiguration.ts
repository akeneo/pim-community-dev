import {LabelCollection} from '@akeneo-pim-community/shared';

export type ColumnType = 'text' | 'number' | 'boolean';
export type ColumnDefinition = {
  code: string;
  data_type: ColumnType;
  labels: LabelCollection;
};

const dataTypes = ['text', 'number', 'boolean'];

export type TableConfiguration = ColumnDefinition[];
export {dataTypes};
