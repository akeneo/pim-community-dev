import React from 'react';
import {FreeText} from '../../../models';
import {Field, TextInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {PropertyEditFieldsProps} from '../PropertyEdit';

const FreeTextEdit: PropertyEditFieldsProps<FreeText> = ({selectedProperty, onChange}) => {
  const translate = useTranslate();
  const onTextChange = (text: string) => {
    selectedProperty.string = text;
    onChange(selectedProperty);
  };

  return (
    <Field label={translate('pim_identifier_generator.structure.settings.free_text.string_label')}>
      <TextInput value={selectedProperty.string} onChange={onTextChange} />
    </Field>
  );
};

export {FreeTextEdit};
