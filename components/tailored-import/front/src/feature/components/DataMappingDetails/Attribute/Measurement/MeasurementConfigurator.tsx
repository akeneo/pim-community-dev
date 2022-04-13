import React from 'react';
import {Field, Helper, SelectInput} from 'akeneo-design-system';
import {filterErrors, useTranslate, getLabel, useUserContext} from '@akeneo-pim-community/shared';
import {AttributeDataMappingConfiguratorProps} from '../../../../models';
import {isMeasurementTarget, MeasurementSourceConfiguration} from './model';
import {InvalidAttributeTargetError} from '../error/InvalidAttributeTargetError';
import {useMeasurementFamily} from '../../../../hooks';
import {
  AttributeTargetParameters,
  ClearIfEmpty,
  DecimalSeparatorField,
  Operations,
  Sources,
} from '../../../../components';

const MeasurementConfigurator = ({
  dataMapping,
  columns,
  attribute,
  validationErrors,
  onOperationsChange,
  onRefreshSampleData,
  onSourcesChange,
  onTargetChange,
}: AttributeDataMappingConfiguratorProps) => {
  const target = dataMapping.target;
  if (!isMeasurementTarget(target)) {
    throw new InvalidAttributeTargetError(
      `Invalid target data "${dataMapping.target.code}" for measurement configurator`
    );
  }

  if (!attribute.metric_family) {
    throw new InvalidAttributeTargetError(`Invalid metric family for measurement configurator`);
  }

  const catalogLocale = useUserContext().get('catalogLocale');
  const translate = useTranslate();
  const decimalSeparatorErrors = filterErrors(validationErrors, '[target][decimal_separator]');
  const unitErrors = filterErrors(validationErrors, '[target][unit]');
  const measurementFamily = useMeasurementFamily(attribute.metric_family);

  const handleSourceConfigurationChange = (sourceConfiguration: MeasurementSourceConfiguration) => {
    onTargetChange({...dataMapping.target, source_configuration: sourceConfiguration});
  };

  return (
    <>
      <AttributeTargetParameters
        attribute={attribute}
        target={dataMapping.target}
        validationErrors={filterErrors(validationErrors, '[target]')}
        onTargetChange={onTargetChange}
      >
        <ClearIfEmpty target={target} onTargetChange={onTargetChange} />
        <Field label={translate('akeneo.tailored_import.data_mapping.target.parameters.measurement_unit.title')}>
          {null !== measurementFamily && (
            <SelectInput
              invalid={0 < unitErrors.length}
              clearable={false}
              emptyResultLabel={translate('pim_common.no_result')}
              openLabel={translate('pim_common.open')}
              value={target.source_configuration.unit}
              onChange={unit => handleSourceConfigurationChange({...target.source_configuration, unit})}
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
          <DecimalSeparatorField
            value={target.source_configuration.decimal_separator}
            onChange={decimalSeparator =>
              handleSourceConfigurationChange({...target.source_configuration, decimal_separator: decimalSeparator})
            }
            validationErrors={decimalSeparatorErrors}
          />
        )}
      </AttributeTargetParameters>
      <Sources
        isMultiSource={true}
        sources={dataMapping.sources}
        columns={columns}
        validationErrors={filterErrors(validationErrors, '[sources]')}
        onSourcesChange={onSourcesChange}
      />
      <Operations
        dataMapping={dataMapping}
        compatibleOperations={[]}
        onOperationsChange={onOperationsChange}
        onRefreshSampleData={onRefreshSampleData}
      />
    </>
  );
};

export {MeasurementConfigurator};
