import {LabelCollection} from '@akeneo-pim-community/shared';

export type CategoryAttributeUUID = string;
export type CategoryAttributeCode = string;

export const CATEGORY_ATTRIBUTE_TYPE_TEXT = 'text';
export const CATEGORY_ATTRIBUTE_TYPE_IMAGE = 'image';

export type CategoryAttributeType = typeof CATEGORY_ATTRIBUTE_TYPE_TEXT | typeof CATEGORY_ATTRIBUTE_TYPE_IMAGE;

export interface CategoryAttributeDefinition {
  uuid: CategoryAttributeUUID;
  code: CategoryAttributeCode;
  type: CategoryAttributeType;
}

export type Attribute = {
  uuid: string;
  code: string;
  type: string;
  order: number;
  is_localizable: boolean;
  labels: LabelCollection;
  template_identifier: string;
};
