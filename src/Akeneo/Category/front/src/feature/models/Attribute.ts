import {LabelCollection} from '@akeneo-pim-community/shared';

export const CATEGORY_ATTRIBUTE_TYPE_TEXT = 'text';
export const CATEGORY_ATTRIBUTE_TYPE_AREA = 'textarea';
export const CATEGORY_ATTRIBUTE_TYPE_RICHTEXT = 'richtext';
export const CATEGORY_ATTRIBUTE_TYPE_IMAGE = 'image';

const TYPES = [
  CATEGORY_ATTRIBUTE_TYPE_TEXT,
  CATEGORY_ATTRIBUTE_TYPE_AREA,
  CATEGORY_ATTRIBUTE_TYPE_RICHTEXT,
  CATEGORY_ATTRIBUTE_TYPE_IMAGE,
] as const;

export type CategoryAttributeType = typeof TYPES[number];

export type Attribute = {
  uuid: string;
  code: string;
  type: CategoryAttributeType;
  order: number;
  is_scopable: boolean;
  is_localizable: boolean;
  labels: LabelCollection;
  template_uuid: string;
};
