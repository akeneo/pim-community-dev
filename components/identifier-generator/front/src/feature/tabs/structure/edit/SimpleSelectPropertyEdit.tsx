import React from 'react';
import {ChannelCode, LocaleCode} from '@akeneo-pim-community/shared';
import {AbbreviationType, CanUseNomenclatureProperty, SimpleSelectProperty} from '../../../models';
import {PropertyEditFieldsProps} from '../PropertyEdit';
import {ScopeAndLocaleSelector} from '../../../components';
import {ProcessablePropertyEdit} from '../ProcessablePropertyEdit';
import {SectionTitle} from 'akeneo-design-system';
import {useGetAttributeLabel} from '../../../hooks';

const options = [
  {value: AbbreviationType.TRUNCATE, label: 'pim_identifier_generator.structure.settings.code_format.type.truncate'},
  {value: AbbreviationType.NO, label: 'pim_identifier_generator.structure.settings.code_format.type.code'},
  {
    value: AbbreviationType.NOMENCLATURE,
    label: 'pim_identifier_generator.structure.settings.code_format.type.nomenclature',
  },
];

const SimpleSelectPropertyEdit: PropertyEditFieldsProps<SimpleSelectProperty> = ({selectedProperty, onChange}) => {
  const label = useGetAttributeLabel(selectedProperty.attributeCode);
  const handleScopeAndLocaleChange = (newValue: {scope?: ChannelCode | null; locale?: LocaleCode | null}) => {
    onChange({
      ...selectedProperty,
      ...newValue,
    });
  };

  const handleChange = (simpleSelectProperty: CanUseNomenclatureProperty) => {
    onChange({
      ...simpleSelectProperty,
      attributeCode: selectedProperty.attributeCode,
    } as SimpleSelectProperty);
  };

  return (
    <>
      <SectionTitle>
        <SectionTitle.Title>{label}</SectionTitle.Title>
      </SectionTitle>
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
    </>
  );
};

export {SimpleSelectPropertyEdit};
