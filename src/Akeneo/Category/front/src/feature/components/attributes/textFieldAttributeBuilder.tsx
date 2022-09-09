import {Attribute} from '../../models';
import React from 'react';
import {Field, TextInput} from 'akeneo-design-system';
import {AttributeFieldBuilder, AttributeInputValue, AttributeProps} from './types';
import {getLabelFromAttribute} from './templateAttributesFactory';

export class TextFieldAttributeBuilder implements AttributeFieldBuilder<AttributeInputValue> {
  buildAttributeField(attr: Attribute): React.FC<AttributeProps<AttributeInputValue>> {
    return ({locale, value, onChange}: AttributeProps<AttributeInputValue>) => {
      if (typeof value !== 'string') {
        return null;
      }

      return (
        <Field label={getLabelFromAttribute(attr, locale)} locale={locale}>
          <TextInput name={attr.code} value={value} onChange={onChange} />
        </Field>
      );
    };
  }
}
