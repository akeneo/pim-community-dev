import {LabelCollection} from '@akeneo-pim-community/shared';

export type OptionCode = string;

export type Option = {
  code: OptionCode;
  labels: LabelCollection;
};

export type PaginateOption = {
  total_count: number;
  matches_count: number;
  items: Option[];
};
