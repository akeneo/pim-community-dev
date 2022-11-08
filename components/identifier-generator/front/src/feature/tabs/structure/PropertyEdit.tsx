import React from 'react';
import {PROPERTY_NAMES, PropertyWithIdentifier} from '../../models';
import {FreeTextEdit} from './edit/FreeTextEdit';

type PropertyEditProps = {
  selectedProperty: PropertyWithIdentifier;
  onChange: (propertyWithId: PropertyWithIdentifier) => void;
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
