import React from 'react';
import {PropertyEditFieldsProps} from '../PropertyEdit';
import {FamilyProperty, SimpleSelectProperty} from '../../../models';
import {AttributePropertyEdit} from '../AttributePropertyEdit';

const FamilyPropertyEdit: PropertyEditFieldsProps<FamilyProperty> = ({selectedProperty, onChange}) => {
  const handleChange = (property: FamilyProperty | SimpleSelectProperty) => {
    onChange(property as FamilyProperty);
  };

  return <AttributePropertyEdit selectedProperty={selectedProperty} onChange={handleChange} />;
};

export {FamilyPropertyEdit};
