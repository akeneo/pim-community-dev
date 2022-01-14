import {
  filterErrors,
  formatParameters,
  getLabel,
  useTranslate,
  useUserContext,
  ValidationError,
} from '@akeneo-pim-community/shared';
import {Field, Helper, NumberInput, SelectInput} from 'akeneo-design-system';
import React, {useMemo} from 'react';
import styled from 'styled-components';
import {MeasurementFamily, Unit} from '../../../../models';
import {useMeasurementFamilies} from '../../../../hooks';

const Container = styled.div`
  display: flex;
  flex-direction: column;
  gap: 20px;
`;

type MeasurementValue = {
  value: string;
  unit: string;
};

type MeasurementValueGeneratorConfiguratorProps = {
  value: MeasurementValue;
  measurementFamilyCode: string;
  validationErrors: ValidationError[];
  onValueChange: (value: MeasurementValue) => void;
};

const MeasurementValueGeneratorConfigurator = ({
  value = {
    value: '',
    unit: '',
  },
  measurementFamilyCode,
  validationErrors,
  onValueChange,
}: MeasurementValueGeneratorConfiguratorProps) => {
  const translate = useTranslate();
  const valueErrors = filterErrors(validationErrors, '[value][value]');
  const unitErrors = filterErrors(validationErrors, '[value][unit]');
  const measurementFamilies = useMeasurementFamilies();

  const units = useMemo(() => {
    if (null === measurementFamilies) return [];

    return measurementFamilies.reduce((acc: Unit[], family: MeasurementFamily) => {
      if (measurementFamilyCode === family.code.toLowerCase()) {
        return [...acc, ...family.units];
      }

      return acc;
    }, []);
  }, [measurementFamilies, measurementFamilyCode]);
  const catalogLocale = useUserContext().get('catalogLocale');

  return (
    <Container>
      <Field
        label={translate('akeneo.syndication.data_mapping_details.sources.static.measurement.generator.value.label')}
        incomplete={false}
      >
        <NumberInput
          invalid={0 < valueErrors.length}
          value={value.value}
          readOnly={false}
          onChange={(newValue: string) => onValueChange({...value, value: newValue})}
        />
        {formatParameters(valueErrors).map((error, key) => (
          <Helper key={key} level="error" inline={true}>
            {translate(error.messageTemplate, error.parameters, error.plural)}
          </Helper>
        ))}
      </Field>
      <Field
        label={translate('akeneo.syndication.data_mapping_details.sources.static.measurement.generator.unit.label')}
        incomplete={false}
      >
        {null !== units && (
          <SelectInput
            clearable={false}
            invalid={0 < unitErrors.length}
            emptyResultLabel={translate('pim_common.no_result')}
            clearLabel={translate('pim_common.clear_value')}
            placeholder={translate(
              'akeneo.syndication.data_mapping_details.sources.operation.measurement_conversion.placeholder'
            )}
            openLabel={translate('pim_common.open')}
            value={value.unit}
            onChange={(newUnitCode: string) => {
              onValueChange({...value, unit: newUnitCode});
            }}
          >
            {units.map(({code, labels}) => (
              <SelectInput.Option key={code} title={getLabel(labels, catalogLocale, code)} value={code}>
                {getLabel(labels, catalogLocale, code)}
              </SelectInput.Option>
            ))}
          </SelectInput>
        )}
        {formatParameters(valueErrors).map((error, key) => (
          <Helper key={key} level="error" inline={true}>
            {translate(error.messageTemplate, error.parameters, error.plural)}
          </Helper>
        ))}
      </Field>
    </Container>
  );
};

export {MeasurementValueGeneratorConfigurator};
