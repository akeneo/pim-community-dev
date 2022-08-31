export type CategoryAttributeUUID = string;
export type CategoryAttributeCode = string;

export const CATEGORY_ATTRIBUTE_TYPE_TEXT = 'text';
export const CATEGORY_ATTRIBUTE_TYPE_IMAGE = 'image';

export type CategoryAttributeType = typeof CATEGORY_ATTRIBUTE_TYPE_TEXT | typeof CATEGORY_ATTRIBUTE_TYPE_IMAGE;

export interface CategoryAttribute {
  uuid: CategoryAttributeUUID;
  code: CategoryAttributeCode;
  type: CategoryAttributeType;
}
