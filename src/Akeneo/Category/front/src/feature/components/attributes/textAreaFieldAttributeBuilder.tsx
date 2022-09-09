import {Attribute} from '../../models';
import React from 'react';
import {Field, TextAreaInput} from 'akeneo-design-system';
import {AttributeFieldBuilder, AttributeInputValue, AttributeProps} from './types';
import {getLabelFromAttribute} from './templateAttributesFactory';

export class TextAreaFieldAttributeBuilder implements AttributeFieldBuilder<AttributeInputValue> {
  buildAttributeField(attr: Attribute): React.FC<AttributeProps<AttributeInputValue>> {
    return ({locale, value, onChange}: AttributeProps<AttributeInputValue>) => {
      if (typeof value !== 'string') {
        return null;
      }

      return (
        <Field label={getLabelFromAttribute(attr, locale)} locale={locale}>
          <TextAreaInput isRichText name={attr.code} value={value} onChange={onChange} />
        </Field>
      );
    };
  }
}
