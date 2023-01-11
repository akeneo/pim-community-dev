import {LabelCollection} from '@akeneo-pim-community/shared';

type Attribute = {
  code: string;
  labels: LabelCollection;
  localizable: boolean;
  scopable: boolean;
};

export type {Attribute};
