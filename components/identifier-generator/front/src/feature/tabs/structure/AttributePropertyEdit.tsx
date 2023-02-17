import React, {useCallback} from 'react';
import {Field, NumberInput, SelectInput} from 'akeneo-design-system';
import {AbbreviationType, FamilyProperty, Operator, SimpleSelectProperty} from '../../models';
import {OperatorSelector} from '../../components';
import {useTranslate} from '@akeneo-pim-community/shared';
import {Styled} from '../../components/Styled';

type Props = {
  selectedProperty: FamilyProperty | SimpleSelectProperty;
  onChange: (property: FamilyProperty | SimpleSelectProperty) => void;
  children?: React.ReactNode;
};

const Operators: Operator[] = [Operator.EQUAL, Operator.EQUAL_OR_LESS];

const options = [
  {value: AbbreviationType.TRUNCATE, label: 'pim_identifier_generator.structure.settings.code_format.type.truncate'},
  {value: AbbreviationType.NO, label: 'pim_identifier_generator.structure.settings.code_format.type.code'},
];

const AttributePropertyEdit: React.FC<Props> = ({selectedProperty, onChange, children}) => {
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
        label={translate('pim_identifier_generator.structure.settings.abbrev_type')}
        requiredLabel={translate('pim_common.required_label')}
      >
        <SelectInput
          value={selectedProperty.process.type}
          emptyResultLabel={translate('pim_identifier_generator.structure.settings.abbrev_type_empty_label')}
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
            label={translate('pim_identifier_generator.structure.settings.operator.title')}
            requiredLabel={translate('pim_common.required_label')}
          >
            <OperatorSelector
              operator={selectedProperty.process.operator || null}
              onChange={onChangeOperator}
              operators={Operators}
            />
          </Field>
          <Field
            label={translate('pim_identifier_generator.structure.settings.chars_number')}
            requiredLabel={translate('pim_common.required_label')}
          >
            <NumberInput value={`${selectedProperty.process.value}`} onChange={onChangeCharsNumber} max={5} min={1} />
          </Field>
        </>
      )}
      {children}
    </Styled.EditionContainer>
  );
};

export {AttributePropertyEdit};
