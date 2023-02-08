import React, {useCallback} from 'react';
import {PropertyEditFieldsProps} from '../PropertyEdit';
import {AbbreviationType, FamilyProperty, FamilyPropertyOperators, Operator} from '../../../models';
import {Field, NumberInput, SelectInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {NomenclatureEdit, OperatorSelector} from '../../../components';
import {Styled} from '../../../components/Styled';
import {useIdentifierGeneratorAclContext} from '../../../context';

const options = [
  {value: AbbreviationType.TRUNCATE, label: 'pim_identifier_generator.structure.settings.code_format.type.truncate'},
  {value: AbbreviationType.NO, label: 'pim_identifier_generator.structure.settings.code_format.type.code'},
  {value: AbbreviationType.NOMENCLATURE, label: 'pim_identifier_generator.structure.settings.code_format.type.nomenclature'}
];

const FamilyPropertyEdit: PropertyEditFieldsProps<FamilyProperty> = ({selectedProperty, onChange}) => {
  const translate = useTranslate();
  const identifierGeneratorAclContext = useIdentifierGeneratorAclContext();

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
      } else if (type === AbbreviationType.NO) {
        onChange({
          type: selectedProperty.type,
          process: {
            type: AbbreviationType.NO,
          },
        });
      } else {
        onChange({
          type: selectedProperty.type,
          process: {
            type: AbbreviationType.NOMENCLATURE,
          }
        })
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
        onChange({
          ...selectedProperty,
          process: {
            ...selectedProperty.process,
            value: charsNumber ? parseInt(charsNumber) : null,
          },
        });
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
          readOnly={!identifierGeneratorAclContext.isManageIdentifierGeneratorAclGranted}
        >
          {options.map(({value, label}) => (
            <SelectInput.Option value={value} title={translate(label)} key={value}>
              {translate(label)}
            </SelectInput.Option>
          ))}
        </SelectInput>
      </Field>
      {selectedProperty.process.type === AbbreviationType.TRUNCATE && (
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
              value={`${selectedProperty.process.value}`}
              onChange={onChangeCharsNumber}
              max={5}
              min={1}
              readOnly={!identifierGeneratorAclContext.isManageIdentifierGeneratorAclGranted}
            />
          </Field>
        </>
      )}
      {selectedProperty.process.type === AbbreviationType.NOMENCLATURE && (
        <NomenclatureEdit />
      )}
    </Styled.EditionContainer>
  );
};

export {FamilyPropertyEdit};
