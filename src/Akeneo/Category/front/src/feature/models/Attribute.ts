import {LabelCollection} from '@akeneo-pim-community/shared';

// TODO keep or remove ?
export const CATEGORY_ATTRIBUTE_TYPE_TEXT = 'text';
export const CATEGORY_ATTRIBUTE_TYPE_IMAGE = 'image';

export type Attribute = {
  uuid: string;
  code: string;
  type: string;
  order: number;
  is_localizable: boolean;
  labels: LabelCollection;
  template_identifier: string;
};
