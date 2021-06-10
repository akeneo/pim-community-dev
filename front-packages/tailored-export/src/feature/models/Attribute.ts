import {LabelCollection} from '@akeneo-pim-community/shared';

type Attribute = {
  code: string;
  type: string;
  labels: LabelCollection;
  scopable: boolean;
  localizable: boolean;
};

export type {Attribute};
