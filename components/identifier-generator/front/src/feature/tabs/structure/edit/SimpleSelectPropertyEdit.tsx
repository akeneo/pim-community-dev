import React from 'react';
import {ChannelCode, LocaleCode} from '@akeneo-pim-community/shared';
import {AbbreviationType, FamilyProperty, SimpleSelectProperty} from '../../../models';
import {PropertyEditFieldsProps} from '../PropertyEdit';
import {ScopeAndLocaleSelector} from '../../../components/ScopeAndLocaleSelector';
import {ProcessablePropertyEdit} from '../ProcessablePropertyEdit';

const options = [
  {value: AbbreviationType.TRUNCATE, label: 'pim_identifier_generator.structure.settings.code_format.type.truncate'},
  {value: AbbreviationType.NO, label: 'pim_identifier_generator.structure.settings.code_format.type.code'},
];

const SimpleSelectPropertyEdit: PropertyEditFieldsProps<SimpleSelectProperty> = ({selectedProperty, onChange}) => {
  const handleScopeAndLocaleChange = (newValue: {scope?: ChannelCode | null; locale?: LocaleCode | null}) => {
    onChange({
      ...selectedProperty,
      ...newValue,
    });
  };

  const handleChange = (simpleSelectProperty: SimpleSelectProperty | FamilyProperty) => {
    onChange({
      ...simpleSelectProperty,
      attributeCode: selectedProperty.attributeCode,
    } as SimpleSelectProperty);
  };

  return (
    <ProcessablePropertyEdit selectedProperty={selectedProperty} onChange={handleChange} options={options}>
      {selectedProperty.attributeCode && (
        <ScopeAndLocaleSelector
          attributeCode={selectedProperty.attributeCode}
          locale={selectedProperty.locale}
          scope={selectedProperty.scope}
          onChange={handleScopeAndLocaleChange}
          isHorizontal={false}
        />
      )}
    </ProcessablePropertyEdit>
  );
};

export {SimpleSelectPropertyEdit};
