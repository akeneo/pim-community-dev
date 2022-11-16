import React from 'react';
import {Property, PROPERTY_NAMES} from '../../models';
import {FreeTextEdit} from './edit/FreeTextEdit';

type PropertyEditProps = {
  selectedProperty: Property;
  onChange: (propertyWithId: Property) => void;
};

const PropertyEdit: React.FC<PropertyEditProps> = ({selectedProperty, onChange}) => {
  return (
    <>
      {PROPERTY_NAMES.FREE_TEXT === selectedProperty.type && (
        <FreeTextEdit selectedProperty={selectedProperty} onChange={onChange} />
      )}
    </>
  );
};

export {PropertyEdit};
