import React from 'react';
import {Field, TextInput} from 'akeneo-design-system';
import {AttributeFieldBuilder, AttributeInputValue, AttributeFieldProps} from './types';
import {getLabelFromAttribute} from './templateAttributesFactory';
import {memoize} from 'lodash/fp';

const unMemoizedBuildTextFieldAttribute: AttributeFieldBuilder<AttributeInputValue> = attribute => {
  const Component: React.FC<AttributeFieldProps<AttributeInputValue>> = ({
    readOnly,
    locale,
    value,
    onChange,
  }: AttributeFieldProps<AttributeInputValue>) => {
    if (typeof value !== 'string') {
      return null;
    }

    return (
      <Field label={getLabelFromAttribute(attribute, locale)} locale={locale}>
        <TextInput readOnly={readOnly} name={attribute.code} value={value} onChange={onChange} />
      </Field>
    );
  };

  Component.displayName = 'TextFieldAttribute';
  return Component;
};

export const buildTextFieldAttribute = memoize(unMemoizedBuildTextFieldAttribute);
