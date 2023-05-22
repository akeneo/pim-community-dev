import React, {useCallback} from 'react';
import {Field, NumberInput, SelectInput} from 'akeneo-design-system';
import {AbbreviationType, CanUseNomenclatureProperty, Operator, RefEntityProperty} from '../../models';
import {NomenclatureEdit, OperatorSelector} from '../../components';
import {useTranslate} from '@akeneo-pim-community/shared';
import {useIdentifierGeneratorAclContext} from '../../context';

type ProcessablePropertyType = CanUseNomenclatureProperty | RefEntityProperty;

type Props = {
  selectedProperty: ProcessablePropertyType;
  onChange: (property: ProcessablePropertyType) => void;
  children?: React.ReactNode;
  options: {value: AbbreviationType; label: string}[];
};

const Operators: Operator[] = [Operator.EQUALS, Operator.LOWER_OR_EQUAL_THAN];

const ProcessablePropertyEdit: React.FC<Props> = ({selectedProperty, onChange, children, options}) => {
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
    <>
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
      {children}
      {selectedProperty.process.type === AbbreviationType.NOMENCLATURE && (
        <NomenclatureEdit selectedProperty={selectedProperty} />
      )}
    </>
  );
};

export {ProcessablePropertyEdit};
