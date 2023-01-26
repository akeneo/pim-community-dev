import React from 'react';
import {PropertyEditFieldsProps} from '../PropertyEdit';
import {AbbreviationType, FamilyCodeOperators, FamilyCodeProperty, Operator} from '../../../models';
import {Field, NumberInput, SelectInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {OperatorSelector} from '../../../components';
import styled from 'styled-components';

const options = [
  {value: AbbreviationType.FIRST_CHAR, label: 'pim_identifier_generator.structure.settings.code_format.type.first_chars'},
  {value: AbbreviationType.CODE, label: 'pim_identifier_generator.structure.settings.code_format.type.code'},
];

const StyledContainer = styled.div`
  display: flex;
  flex-direction: column;
  gap: 20px;
`;

const FamilyCodeEdit: PropertyEditFieldsProps<FamilyCodeProperty> = ({selectedProperty, onChange}) => {
  const translate = useTranslate();

  const handleChange = (value: string) => {
    const charsNumber = value === AbbreviationType.FIRST_CHAR ? 3 : null;
    onChange({...selectedProperty, abbreviation_type: value as AbbreviationType, charsNumber});
  };

  const onChangeOperator = (operator: Operator) => {
    onChange({...selectedProperty, operator});
  };

  const onChangeCharsNumber = (charsNumber: string) => {
    onChange({...selectedProperty, charsNumber: parseInt(charsNumber)});
  };

  return (
    <StyledContainer>
      <Field
        label={translate('pim_identifier_generator.structure.settings.family.abbrev_type')}
        requiredLabel={translate('pim_common.required_label')}
      >
        <SelectInput
          value={selectedProperty.abbreviation_type}
          emptyResultLabel={translate('pim_identifier_generator.structure.settings.family.abbrev_type_empty_label')}
          openLabel={translate('pim_common.open')}
          onChange={handleChange}
          clearable={false}
          >
          {options.map(({value, label}) => (
            <SelectInput.Option value={value} title={translate(label)} key={value}>
              {translate(label)}
            </SelectInput.Option>
          ))}
        </SelectInput>
      </Field>
      {selectedProperty.abbreviation_type === AbbreviationType.FIRST_CHAR && selectedProperty.charsNumber && (
        <>
          <Field
            label={translate('pim_identifier_generator.structure.settings.family.operator')}
            requiredLabel={translate('pim_common.required_label')}
          >
            <OperatorSelector
              operator={selectedProperty.operator}
              onChange={onChangeOperator}
              operators={FamilyCodeOperators}
              />
          </Field>
          <Field
            label={translate('pim_identifier_generator.structure.settings.family.chars_number')}
            requiredLabel={translate('pim_common.required_label')}
          >
            <NumberInput
              value={selectedProperty.charsNumber.toString()}
              onChange={onChangeCharsNumber}
              maxLength={5}
            />
          </Field>
        </>
      )}
    </StyledContainer>
  );
};

export {FamilyCodeEdit};
