import { LabelCollection } from '@akeneo-pim-community/shared';

export type ColumnType = 'text';
export type ColumnDefinition = {
  code: string;
  data_type: ColumnType;
  labels: LabelCollection;
};

export type TableConfiguration = ColumnDefinition[];
