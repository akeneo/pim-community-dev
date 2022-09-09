import {FileInfo} from 'akeneo-design-system';
import {Attribute} from '../../models';
import React from 'react';

export type TextAttributeInputValue = string;

export type ImageAttributeInputValue = FileInfo | null;

export type AttributeInputValue = TextAttributeInputValue | ImageAttributeInputValue;

export interface AttributeFieldBuilder<ValueType extends AttributeInputValue> {
  buildAttributeField(attr: Attribute): React.FC<AttributeProps<ValueType>>;
}

export type AttributeProps<ValueType> = {
  locale: string;
  value: ValueType;
  onChange: (value: ValueType) => void;
};
