import React, {useCallback} from 'react';
import {ChannelCode, LocaleCode, useTranslate} from '@akeneo-pim-community/shared';
import {AbbreviationType, FamilyPropertyOperators, Operator, SimpleSelectProperty} from '../../models';
import {Styled} from '../../components/Styled';
import {Field, NumberInput, SelectInput} from 'akeneo-design-system';
import {OperatorSelector} from '../../components';
import {PropertyEditFieldsProps} from './PropertyEdit';
import {ScopeAndLocaleSelector} from '../../components/ScopeAndLocaleSelector';

const options = [
  {value: AbbreviationType.TRUNCATE, label: 'pim_identifier_generator.structure.settings.code_format.type.truncate'},
  {value: AbbreviationType.NO, label: 'pim_identifier_generator.structure.settings.code_format.type.code'},
];

const SimpleSelectPropertyEdit: PropertyEditFieldsProps<SimpleSelectProperty> = ({selectedProperty, onChange}) => {
  const translate = useTranslate();

  const onChangeProcessType = useCallback(
    (type: string) => {
      if (type === AbbreviationType.TRUNCATE) {
        onChange({
          type: selectedProperty.type,
          attributeCode: selectedProperty.attributeCode,
          process: {
            type: AbbreviationType.TRUNCATE,
            value: 3,
            operator: null,
          },
        });
      } else {
        onChange({
          type: selectedProperty.type,
          attributeCode: selectedProperty.attributeCode,
          process: {
            type: AbbreviationType.NO,
          },
        });
      }
    },
    [onChange, selectedProperty.attributeCode, selectedProperty.type]
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

  const handleScopeAndLocaleChange = (newValue: {scope?: ChannelCode | null; locale?: LocaleCode | null}) => {
    onChange({
      ...selectedProperty,
      ...newValue,
    });
  };

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
            <NumberInput value={`${selectedProperty.process.value}`} onChange={onChangeCharsNumber} max={5} min={1} />
          </Field>
        </>
      )}
      <ScopeAndLocaleSelector
        attributeCode={selectedProperty.attributeCode}
        locale={selectedProperty.locale}
        scope={selectedProperty.scope}
        onChange={handleScopeAndLocaleChange}
        isHorizontal={false}
      />
    </Styled.EditionContainer>
  );
};

export {SimpleSelectPropertyEdit};
