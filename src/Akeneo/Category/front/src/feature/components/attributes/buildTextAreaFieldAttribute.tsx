import React from 'react';
import {Field, TextAreaInput} from 'akeneo-design-system';
import {AttributeFieldBuilder, AttributeInputValue, AttributeFieldProps} from './types';
import {getLabelFromAttribute} from './templateAttributesFactory';
import {memoize} from 'lodash/fp';

const unMemoizedBuildTextAreaFieldAttribute: AttributeFieldBuilder<AttributeInputValue> = attribute => {
  const Component: React.FC<AttributeFieldProps<AttributeInputValue>> = ({
    channel,
    locale,
    value,
    onChange,
  }: AttributeFieldProps<AttributeInputValue>) => {
    if (typeof value !== 'string') {
      return null;
    }

    return (
      <Field label={getLabelFromAttribute(attribute, locale)} channel={channel.label} locale={locale}>
        <TextAreaInput name={attribute.code} value={value} onChange={onChange} />
      </Field>
    );
  };

  Component.displayName = 'TextAreaFieldAttribute';
  return Component;
};

export const buildTextAreaFieldAttribute = memoize(unMemoizedBuildTextAreaFieldAttribute);
