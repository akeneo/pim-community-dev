import {Attribute} from '../../models';
import React from 'react';
import {Field, TextAreaInput} from 'akeneo-design-system';
import {AttributeFieldBuilder, AttributeInputValue, AttributeProps} from './types';
import {getLabelFromAttribute} from './templateAttributesFactory';
import styled from 'styled-components';

export class RichTextFieldAttributeBuilder implements AttributeFieldBuilder<AttributeInputValue> {
  buildAttributeField(attr: Attribute): React.FC<AttributeProps<AttributeInputValue>> {
    const Field960 = styled(Field)`
      max-width: 960px;
    `;

    return ({locale, value, onChange}: AttributeProps<AttributeInputValue>) => {
      if (typeof value !== 'string') {
        return null;
      }

      return (
        <Field960 label={getLabelFromAttribute(attr, locale)} locale={locale}>
          <TextAreaInput isRichText name={attr.code} value={value} onChange={onChange} />
        </Field960>
      );
    };
  }
}
