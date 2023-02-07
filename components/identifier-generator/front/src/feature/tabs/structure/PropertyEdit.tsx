import React from 'react';
import {Property, PROPERTY_NAMES} from '../../models';
import {AutoNumberEdit, FamilyPropertyEdit, FreeTextEdit} from './edit/';
import {SectionTitle} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';

type PropertyEditProps = {
  selectedProperty: Property;
  onChange: (property: Property) => void;
};

export type PropertyEditFieldsProps<T extends Property> = React.FC<{
  selectedProperty: T;
  onChange: (property: T) => void;
}>;

const components = {
  [PROPERTY_NAMES.FREE_TEXT]: FreeTextEdit,
  [PROPERTY_NAMES.AUTO_NUMBER]: AutoNumberEdit,
  [PROPERTY_NAMES.FAMILY]: FamilyPropertyEdit,
};

const PropertyEdit: React.FC<PropertyEditProps> = ({selectedProperty, onChange}) => {
  const translate = useTranslate();

  const Component = components[selectedProperty.type] as PropertyEditFieldsProps<Property>;

  return (
    <div>
      <SectionTitle>
        <SectionTitle.Title>
          {translate(`pim_identifier_generator.structure.settings.${selectedProperty.type}.title`)}
        </SectionTitle.Title>
      </SectionTitle>
      <Component selectedProperty={selectedProperty} onChange={onChange} />
    </div>
  );
};

export {PropertyEdit};
