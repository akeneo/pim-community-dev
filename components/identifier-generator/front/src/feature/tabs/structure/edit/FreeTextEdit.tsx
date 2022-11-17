import React from 'react';
import {FreeText, Property, PROPERTY_NAMES} from '../../../models';
import {Field, SectionTitle, TextInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';

type FreeTextEditProps = {
  selectedProperty: FreeText;
  onChange: (propertyWithId: Property) => void;
};

const FreeTextEdit: React.FC<FreeTextEditProps> = ({selectedProperty, onChange}) => {
  const translate = useTranslate();
  const onTextChange = (text: string) => {
    selectedProperty.string = text;
    onChange(selectedProperty);
  };

  return (
    <>
      <SectionTitle>
        <SectionTitle.Title level="secondary">
          {translate(`pim_identifier_generator.structure.settings.${PROPERTY_NAMES.FREE_TEXT}.title`)}
        </SectionTitle.Title>
      </SectionTitle>
      <Field label={translate(`pim_identifier_generator.structure.settings.${PROPERTY_NAMES.FREE_TEXT}.string_label`)}>
        <TextInput value={selectedProperty.string} onChange={onTextChange} />
      </Field>
    </>
  );
};

export {FreeTextEdit};
