import React from 'react';
import {PropertyEditFieldsProps} from '../PropertyEdit';
import {AbbreviationType, FamilyProperty, SimpleSelectProperty} from '../../../models';
import {ProcessablePropertyEdit} from '../ProcessablePropertyEdit';

const options = [
  {value: AbbreviationType.TRUNCATE, label: 'pim_identifier_generator.structure.settings.code_format.type.truncate'},
  {value: AbbreviationType.NO, label: 'pim_identifier_generator.structure.settings.code_format.type.code'},
  {
    value: AbbreviationType.NOMENCLATURE,
    label: 'pim_identifier_generator.structure.settings.code_format.type.nomenclature',
  },
];

const FamilyPropertyEdit: PropertyEditFieldsProps<FamilyProperty> = ({selectedProperty, onChange}) => {
  const handleChange = (property: FamilyProperty | SimpleSelectProperty) => {
    onChange(property as FamilyProperty);
  };

  return <ProcessablePropertyEdit selectedProperty={selectedProperty} onChange={handleChange} options={options} />;
};

export {FamilyPropertyEdit};
