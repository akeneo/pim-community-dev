import React from 'react';
import {ChannelCode, LocaleCode} from '@akeneo-pim-community/shared';
import {FamilyProperty, SimpleSelectProperty} from '../../../models';
import {PropertyEditFieldsProps} from '../PropertyEdit';
import {ScopeAndLocaleSelector} from '../../../components/ScopeAndLocaleSelector';
import {AttributePropertyEdit} from '../AttributePropertyEdit';

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
    <AttributePropertyEdit selectedProperty={selectedProperty} onChange={handleChange}>
      {selectedProperty.attributeCode && (
        <ScopeAndLocaleSelector
          attributeCode={selectedProperty.attributeCode}
          locale={selectedProperty.locale}
          scope={selectedProperty.scope}
          onChange={handleScopeAndLocaleChange}
          isHorizontal={false}
        />
      )}
    </AttributePropertyEdit>
  );
};

export {SimpleSelectPropertyEdit};
