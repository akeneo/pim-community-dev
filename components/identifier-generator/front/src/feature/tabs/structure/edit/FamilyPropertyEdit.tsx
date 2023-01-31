import React, {useCallback} from 'react';
import {PropertyEditFieldsProps} from '../PropertyEdit';
import {AbbreviationType, FamilyProperty, FamilyPropertyOperators, Operator} from '../../../models';
import {Field, NumberInput, SelectInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {OperatorSelector} from '../../../components';
import {Styled} from '../../../components/Styled';

const options = [
  {value: AbbreviationType.TRUNCATE, label: 'pim_identifier_generator.structure.settings.code_format.type.truncate'},
  {value: AbbreviationType.NO, label: 'pim_identifier_generator.structure.settings.code_format.type.code'},
];

const FamilyPropertyEdit: PropertyEditFieldsProps<FamilyProperty> = ({selectedProperty, onChange}) => {
  const translate = useTranslate();

  const onChangeProcessType = useCallback(
    (type: string) => {
      if (type === AbbreviationType.TRUNCATE) {
        onChange({
          type: selectedProperty.type,
          process: {
            type: AbbreviationType.TRUNCATE,
            value: 3,
            operator: null,
          },
        });
      } else {
        onChange({
          type: selectedProperty.type,
          process: {
            type: AbbreviationType.NO,
          },
        });
      }
    },
    [onChange, selectedProperty.type]
  );

  const onChangeOperator = useCallback(
    (operator: Operator) => {
      if (selectedProperty.process.type === AbbreviationType.TRUNCATE) {
        onChange({...selectedProperty, process: {...selectedProperty.process, operator}});
      }
    },
    [onChange, selectedProperty]
  );

  const onChangeCharsNumber = useCallback(
    (charsNumber: string) => {
      if (selectedProperty.process.type === AbbreviationType.TRUNCATE) {
        onChange({...selectedProperty, process: {...selectedProperty.process, value: parseInt(charsNumber)}});
      }
    },
    [onChange, selectedProperty]
  );

  return (
    <Styled.EditionContainer>
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
              operators={FamilyPropertyOperators}
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
    </Styled.EditionContainer>
  );
};

export {FamilyPropertyEdit};
