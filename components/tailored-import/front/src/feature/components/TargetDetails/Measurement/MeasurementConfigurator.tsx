import {AttributeTargetParameterConfiguratorProps} from '../../../models';
import {
  availableDecimalSeparators,
  isMeasurementDecimalSeparator,
  isMeasurementTarget,
  MeasurementSourceParameter,
} from './model';
import {InvalidAttributeTargetError} from '../error/InvalidAttributeTargetError';
import {Field, Helper, SelectInput} from 'akeneo-design-system';
import {filterErrors, useTranslate, getLabel, useUserContext} from '@akeneo-pim-community/shared';
import React from 'react';
import {useMeasurementFamily} from '../../../hooks/useMeasurementFamily';

const MeasurementConfigurator = ({
  target,
  attribute,
  onTargetAttributeChange,
  validationErrors,
}: AttributeTargetParameterConfiguratorProps) => {
  if (!isMeasurementTarget(target)) {
    throw new InvalidAttributeTargetError(`Invalid target data "${target.code}" for measurement configurator`);
  }

  if (!attribute.metric_family) {
    throw new InvalidAttributeTargetError(`Invalid metric family for measurement configurator`);
  }

  const catalogLocale = useUserContext().get('catalogLocale');
  const translate = useTranslate();
  const decimalSeparatorErrors = filterErrors(validationErrors, '[decimal_separator]');
  const unitErrors = filterErrors(validationErrors, '[unit]');
  const measurementFamily = useMeasurementFamily(attribute.metric_family);

  const handleAttributeTargetChange = (updatedMeasurementSourceParameter: MeasurementSourceParameter) => {
    onTargetAttributeChange({...target, source_parameter: updatedMeasurementSourceParameter});
  };

  return (
    <>
      <Field label={translate('akeneo.tailored_import.data_mapping.target.parameters.measurement_unit.title')}>
        {null !== measurementFamily && (
          <SelectInput
            invalid={0 < unitErrors.length}
            clearable={false}
            emptyResultLabel={translate('pim_common.no_result')}
            openLabel={translate('pim_common.open')}
            value={target.source_parameter.unit}
            onChange={unit => {
              handleAttributeTargetChange({...target.source_parameter, unit});
            }}
          >
            {measurementFamily.units.map(({code, labels}) => (
              <SelectInput.Option key={code} title={getLabel(labels, catalogLocale, code)} value={code}>
                {getLabel(labels, catalogLocale, code)}
              </SelectInput.Option>
            ))}
          </SelectInput>
        )}
        {unitErrors.map((error, index) => (
          <Helper key={index} inline={true} level="error">
            {translate(error.messageTemplate, error.parameters)}
          </Helper>
        ))}
      </Field>

      {attribute.decimals_allowed && (
        <Field label={translate('akeneo.tailored_import.data_mapping.target.parameters.decimal_separator.title')}>
          <SelectInput
            invalid={0 < decimalSeparatorErrors.length}
            clearable={false}
            emptyResultLabel={translate('pim_common.no_result')}
            openLabel={translate('pim_common.open')}
            value={target.source_parameter.decimal_separator}
            onChange={decimal_separator => {
              if (isMeasurementDecimalSeparator(decimal_separator)) {
                handleAttributeTargetChange({...target.source_parameter, decimal_separator});
              }
            }}
          >
            {Object.entries(availableDecimalSeparators).map(([separator, name]) => (
              <SelectInput.Option
                key={separator}
                title={translate(`akeneo.tailored_import.data_mapping.target.parameters.decimal_separator.${name}`)}
                value={separator}
              >
                {translate(`akeneo.tailored_import.data_mapping.target.parameters.decimal_separator.${name}`)}
              </SelectInput.Option>
            ))}
          </SelectInput>
          {decimalSeparatorErrors.map((error, index) => (
            <Helper key={index} inline={true} level="error">
              {translate(error.messageTemplate, error.parameters)}
            </Helper>
          ))}
        </Field>
      )}
    </>
  );
};

export {MeasurementConfigurator};
