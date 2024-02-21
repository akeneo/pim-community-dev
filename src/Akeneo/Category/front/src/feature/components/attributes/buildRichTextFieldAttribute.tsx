import React from 'react';
import {Field, TextAreaInput} from 'akeneo-design-system';
import {AttributeFieldBuilder, AttributeFieldProps, AttributeInputValue} from './types';
import {getLabelFromAttribute} from './templateAttributesFactory';
import styled from 'styled-components';
import {memoize} from 'lodash/fp';
import {buildCompositeKey} from '../../models';

const Field960 = styled(Field)`
  max-width: 960px;
`;

const unMemoizedBuildRichTextFieldAttribute: AttributeFieldBuilder<AttributeInputValue> = attribute => {
  const Component: React.FC<AttributeFieldProps<AttributeInputValue>> = ({
    channel,
    locale,
    value,
    onChange,
  }: AttributeFieldProps<AttributeInputValue>) => {
    if (typeof value !== 'string') {
      return null;
    }

    // TextAreaInput prop "key" = composite key (code|uuid|channel|locale):
    // because the RichTextEditor in the DSM is not able of considering a changed value
    // it loops internally on its state for the value and ignores external modifications of the value
    // we have to force react to rebuild it when changing the value (when channel or locale is changed for instance)
    return (
      <Field960 label={getLabelFromAttribute(attribute, locale)} channel={channel.label} locale={locale}>
        <TextAreaInput
          key={buildCompositeKey(attribute, channel.code, locale)}
          isRichText
          name={attribute.code}
          value={value}
          onChange={onChange}
        />
      </Field960>
    );
  };

  Component.displayName = 'RichTextFieldAttribute';
  return Component;
};

export const buildRichTextFieldAttribute = memoize(unMemoizedBuildRichTextFieldAttribute);
