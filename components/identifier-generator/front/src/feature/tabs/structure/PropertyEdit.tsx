import React from 'react';
import {Property, PROPERTY_NAMES} from '../../models';
import {AutoNumberEdit, FamilyPropertyEdit, FreeTextEdit, AttributePropertyEdit} from './edit/';
import {Styled} from '../../components/Styled';

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
  [PROPERTY_NAMES.SIMPLE_SELECT]: AttributePropertyEdit,
  [PROPERTY_NAMES.REF_ENTITY]: AttributePropertyEdit,
};

const PropertyEdit: React.FC<PropertyEditProps> = ({selectedProperty, onChange}) => {
  const Component = components[selectedProperty.type] as PropertyEditFieldsProps<Property>;

  return (
    <Styled.PropertyFormContainer>
      <Component selectedProperty={selectedProperty} onChange={onChange} />
    </Styled.PropertyFormContainer>
  );
};

export {PropertyEdit};
