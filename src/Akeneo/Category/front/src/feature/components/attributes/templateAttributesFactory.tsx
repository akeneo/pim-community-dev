import React from 'react';
import {Attribute} from '../../models';
import {buildImageFieldAttribute} from './buildImageFieldAttribute';
import {buildRichTextFieldAttribute} from './buildRichTextFieldAttribute';
import {buildTextAreaFieldAttribute} from './buildTextAreaFieldAttribute';
import {buildTextFieldAttribute} from './buildTextFieldAttribute';
import {AttributeFieldBuilder, AttributeInputValue, AttributeFieldProps} from './types';

export const getLabelFromAttribute = (attribute: Attribute, locale: string): string =>
    (attribute?.labels[locale]) ? attribute.labels[locale] : `[${attribute.code}]`;

const attributeFieldBuilders: {[attributeType: string]: AttributeFieldBuilder<AttributeInputValue>} = {
  text: buildTextFieldAttribute,
  richtext: buildRichTextFieldAttribute,
  textarea: buildTextAreaFieldAttribute,
  image: buildImageFieldAttribute,
};

const attributeFieldFactory = (attribute: Attribute): React.FC<AttributeFieldProps<AttributeInputValue>> | null => {
  const builder = attributeFieldBuilders[attribute.type];
  if (!builder) {
    return null;
  }
  return builder(attribute);
};

export {attributeFieldFactory};
