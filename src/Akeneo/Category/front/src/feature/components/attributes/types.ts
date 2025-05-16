import {ChannelCode} from '@akeneo-pim-community/shared';
import {FileInfo} from 'akeneo-design-system';
import React from 'react';
import {Attribute, CATEGORY_ATTRIBUTE_TYPE_IMAGE} from '../../models';

export type TextAttributeInputValue = string;

export type ImageAttributeInputValue = FileInfo | null;

export type ChannelTranslationAttributeValue = {
  code: ChannelCode;
  label: string;
};

export type AttributeInputValue = TextAttributeInputValue | ImageAttributeInputValue | null;

export type AttributeFieldBuilder<ValueType extends AttributeInputValue> = (
  attribute: Attribute
) => React.FC<AttributeFieldProps<ValueType>>;

export type AttributeFieldProps<ValueType> = {
  channel: ChannelTranslationAttributeValue;
  locale: string;
  value: ValueType;
  onChange: (value: ValueType) => void;
};

export const isImageAttributeInputValue = (value: AttributeInputValue): value is ImageAttributeInputValue =>
  value !== null && value.hasOwnProperty('originalFilename') && value.hasOwnProperty('filePath');

export const buildDefaultAttributeInputValue = (attributeType: string): AttributeInputValue => {
  if (attributeType === CATEGORY_ATTRIBUTE_TYPE_IMAGE) {
    return null;
  }
  return '';
};
