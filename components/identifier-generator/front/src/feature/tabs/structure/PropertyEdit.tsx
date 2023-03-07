import React from 'react';
import {Property, PROPERTY_NAMES} from '../../models';
import {AutoNumberEdit, FamilyPropertyEdit, FreeTextEdit, SimpleSelectPropertyEdit} from './edit/';

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
  [PROPERTY_NAMES.SIMPLE_SELECT]: SimpleSelectPropertyEdit,
};

const PropertyEdit: React.FC<PropertyEditProps> = ({selectedProperty, onChange}) => {
  const Component = components[selectedProperty.type] as PropertyEditFieldsProps<Property>;

  return (
    <div>
      <Component selectedProperty={selectedProperty} onChange={onChange} />
    </div>
  );
};

export {PropertyEdit};
