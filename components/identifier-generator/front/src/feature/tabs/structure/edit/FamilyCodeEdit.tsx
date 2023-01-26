import React from 'react';
import {PropertyEditFieldsProps} from '../PropertyEdit';
import {AbbreviationType, FamilyCodeOperators, FamilyCodeProperty, Operator} from '../../../models';
import {Field, NumberInput, SelectInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {OperatorSelector} from '../../../components';
import styled from 'styled-components';

const options = [
  {value: AbbreviationType.TRUNCATE, label: 'pim_identifier_generator.structure.settings.code_format.type.first_chars'},
  {value: AbbreviationType.NO, label: 'pim_identifier_generator.structure.settings.code_format.type.code'},
];

const StyledContainer = styled.div`
  display: flex;
  flex-direction: column;
  gap: 20px;
`;

const FamilyCodeEdit: PropertyEditFieldsProps<FamilyCodeProperty> = ({selectedProperty, onChange}) => {
  const translate = useTranslate();

  const onChangeProcessType = (type: string) => {
    //const charsNumber = value === AbbreviationType.TRUNCATE ? 3 : null;
    if (type === AbbreviationType.TRUNCATE) {
      onChange({
        type: selectedProperty.type,
        process: {
          type: type as AbbreviationType,
          value: 3,
          operator: null,
        },
      });
    } else {
      onChange({
        type: selectedProperty.type,
        process: {
          type: type as AbbreviationType,
        },
      });
    }
  };

  const onChangeOperator = (operator: Operator) => {
    onChange({...selectedProperty, process: {...selectedProperty.process, operator}});
  };

  const onChangeCharsNumber = (charsNumber: string) => {
    onChange({...selectedProperty, process: {...selectedProperty.process, value: parseInt(charsNumber)}});
  };

  return (
    <StyledContainer>
      <Field
        label={translate('pim_identifier_generator.structure.settings.family.abbrev_type')}
        requiredLabel={translate('pim_common.required_label')}
      >
        <SelectInput
          value={selectedProperty.process.type}
          emptyResultLabel={translate('pim_identifier_generator.structure.settings.family.abbrev_type_empty_label')}
          openLabel={translate('pim_common.open')}
          onChange={onChangeProcessType}
          clearable={false}
        >
          {options.map(({value, label}) => (
            <SelectInput.Option value={value} title={translate(label)} key={value}>
              {translate(label)}
            </SelectInput.Option>
          ))}
        </SelectInput>
      </Field>
      {selectedProperty.process.type === AbbreviationType.TRUNCATE && selectedProperty.process.value && (
        <>
          <Field
            label={translate('pim_identifier_generator.structure.settings.family.operator')}
            requiredLabel={translate('pim_common.required_label')}
          >
            <OperatorSelector
              operator={selectedProperty.process.operator || null}
              onChange={onChangeOperator}
              operators={FamilyCodeOperators}
            />
          </Field>
          <Field
            label={translate('pim_identifier_generator.structure.settings.family.chars_number')}
            requiredLabel={translate('pim_common.required_label')}
          >
            <NumberInput
              value={selectedProperty.process.value.toString()}
              onChange={onChangeCharsNumber}
              max={5}
              min={1}
            />
          </Field>
        </>
      )}
    </StyledContainer>
  );
};

export {FamilyCodeEdit};
