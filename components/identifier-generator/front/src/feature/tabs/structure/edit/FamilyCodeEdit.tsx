import React from 'react';
import {PropertyEditFieldsProps} from '../PropertyEdit';
import {AbbreviationType, FamilyCodeOperators, FamilyCodeProperty, Operator} from '../../../models';
import {Field, SelectInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {OperatorSelector} from '../../../components';

const options = [
  {value: AbbreviationType.FIRST_CHAR, label: 'pim_identifier_generator.structure.settings.code_format.type.first_chars'},
  {value: AbbreviationType.CODE, label: 'pim_identifier_generator.structure.settings.code_format.type.code'},
  {value: AbbreviationType.NOMENCLATURE, label: 'pim_identifier_generator.structure.settings.code_format.type.nomenclature'},
];

const FamilyCodeEdit: PropertyEditFieldsProps<FamilyCodeProperty> = ({selectedProperty, onChange}) => {
  const translate = useTranslate();
  console.log({selectedProperty});

  const handleChange = (value: string) => {
    onChange({...selectedProperty, abbreviation_type: value as AbbreviationType});
  };

  const onChangeOperator = (operator: Operator) => {
    onChange({...selectedProperty, operator});
  };

  return (
    <div>
      <Field label={translate('pim_identifier_generator.structure.settings.family.abbrev_type')}>
        <SelectInput
          value={selectedProperty.abbreviation_type}
          emptyResultLabel={translate('pim_identifier_generator.structure.settings.family.abbrev_type_empty_label')}
          openLabel={translate('pim_common.open')}
          onChange={handleChange}
          clearable={false}
          >
          {options.map(({value, label}) => (
            <SelectInput.Option value={value} title={translate(label)} key={value} />
          ))}
        </SelectInput>
      </Field>
      {selectedProperty.abbreviation_type === AbbreviationType.FIRST_CHAR && (
        <>
          <Field label={translate('pim_identifier_generator.structure.settings.family.operator')}>
            <OperatorSelector
              operator={selectedProperty.operator}
              onChange={onChangeOperator}
              operators={FamilyCodeOperators}
              />
          </Field>
        </>
      )}
    </div>
  );
};

export {FamilyCodeEdit};
