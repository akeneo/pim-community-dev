import {LabelCollection} from '@akeneo-pim-community/shared';

type AttributeCode = string;

type Attribute = {
  code: AttributeCode;
  labels: LabelCollection;
  localizable: boolean;
  scopable: boolean;
  type: string;
};

export type {Attribute, AttributeCode};
