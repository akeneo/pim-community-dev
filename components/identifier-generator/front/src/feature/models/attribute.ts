import {LabelCollection} from '@akeneo-pim-community/shared';

type AttributeCode = string;

export enum ATTRIBUTE_TYPE {
  TEXT = 'pim_catalog_text',
  SIMPLE_SELECT = 'pim_catalog_simpleselect',
  MULTI_SELECT = 'pim_catalog_multiselect',
}

type AttributeType = ATTRIBUTE_TYPE;

type Attribute = {
  code: AttributeCode;
  labels: LabelCollection;
  localizable: boolean;
  scopable: boolean;
  type: string;
  reference_data_name?: string;
};

export type {Attribute, AttributeType, AttributeCode};
