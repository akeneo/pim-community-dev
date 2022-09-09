import React from 'react';
import {Attribute} from '../../models';
import {AttributeFieldBuilder, AttributeInputValue, AttributeProps, ImageAttributeInputValue} from './types';
import {TextFieldAttributeBuilder} from './textFieldAttributeBuilder';
import {RichTextFieldAttributeBuilder} from './richTextFieldAttributeBuilder';
import {TextAreaFieldAttributeBuilder} from './textAreaFieldAttributeBuilder';
import {ImageFieldAttributeBuilder} from './imageFieldAttributeBuilder';

export const isImageAttributeInputValue = (value: AttributeInputValue): value is ImageAttributeInputValue =>
  value !== null && value.hasOwnProperty('originalFilename') && value.hasOwnProperty('filePath');

export const getLabelFromAttribute = (attr: Attribute, locale: string): string =>
  attr.labels[locale] ?? '[' + attr.code + ']';

const attributeFieldBuilders: {[attributeType: string]: AttributeFieldBuilder<AttributeInputValue>} = {
  text: new TextFieldAttributeBuilder(),
  richtext: new RichTextFieldAttributeBuilder(),
  textarea: new TextAreaFieldAttributeBuilder(),
  image: new ImageFieldAttributeBuilder(),
};

const attributeFieldFactory = (attr: Attribute): React.FC<AttributeProps<AttributeInputValue>> | null => {
  const builder = attributeFieldBuilders[attr.type];
  if (!builder) {
    return null;
  }
  return builder.buildAttributeField(attr);
};

export {attributeFieldFactory};
