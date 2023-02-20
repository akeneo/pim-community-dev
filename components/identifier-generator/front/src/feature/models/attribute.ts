import {LabelCollection} from '@akeneo-pim-community/shared';

type AttributeCode = string;

type Attribute = {
  code: AttributeCode;
  labels: LabelCollection;
  localizable: boolean;
  scopable: boolean;
};

export type {Attribute, AttributeCode};
