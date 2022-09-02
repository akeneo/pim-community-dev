import {LabelCollection} from '@akeneo-pim-community/shared';

export type AttributeOptionCode = string;

export type AttributeOption = {
  code: AttributeOptionCode;
  labels: LabelCollection;
};
