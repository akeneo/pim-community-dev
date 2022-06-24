import React from 'react';
import {Field, Helper, SelectInput} from 'akeneo-design-system';
import {filterErrors, useTranslate} from '@akeneo-pim-community/shared';
import {isDateTarget, DateSourceConfiguration, isDateFormat, availableDateFormats} from './model';
import {AttributeDataMappingConfiguratorProps, AttributeTarget} from '../../../../models';
import {InvalidAttributeTargetError} from '../error/InvalidAttributeTargetError';
import {AttributeTargetParameters, Operations, Sources, ClearIfEmpty} from '../../../../components';

const DateConfigurator = ({
  dataMapping,
  attribute,
  columns,
  validationErrors,
  onOperationsChange,
  onRefreshSampleData,
  onSourcesChange,
  onTargetChange,
}: AttributeDataMappingConfiguratorProps) => {
  const translate = useTranslate();
  const target = dataMapping.target;
  const dateFormatErrors = filterErrors(validationErrors, '[target][source_configuration][date_format]');

  if (!isDateTarget(target)) {
    throw new InvalidAttributeTargetError(`Invalid target data "${target.code}" for date configurator`);
  }

  const handleSourceConfigurationChange = (updatedSourceConfiguration: DateSourceConfiguration) =>
    onTargetChange({...target, source_configuration: updatedSourceConfiguration});

  return (
    <>
      <AttributeTargetParameters
        attribute={attribute}
        target={dataMapping.target}
        validationErrors={filterErrors(validationErrors, '[target]')}
        onTargetChange={onTargetChange}
      >
        <ClearIfEmpty<AttributeTarget> target={target} onTargetChange={onTargetChange} />
        <Field label={translate('akeneo.tailored_import.data_mapping.target.parameters.date_format')}>
          <SelectInput
            invalid={0 < dateFormatErrors.length}
            clearable={false}
            emptyResultLabel={translate('pim_common.no_result')}
            openLabel={translate('pim_common.open')}
            value={target.source_configuration.date_format}
            onChange={dateFormat => {
              if (isDateFormat(dateFormat)) {
                handleSourceConfigurationChange({...target.source_configuration, date_format: dateFormat});
              }
            }}
          >
            {availableDateFormats.map(availableDateFormat => (
              <SelectInput.Option key={availableDateFormat} title={availableDateFormat} value={availableDateFormat}>
                {availableDateFormat}
              </SelectInput.Option>
            ))}
          </SelectInput>
          {dateFormatErrors.map((error, index) => (
            <Helper key={index} inline={true} level="error">
              {translate(error.messageTemplate, error.parameters)}
            </Helper>
          ))}
        </Field>
      </AttributeTargetParameters>
      <Sources
        isMultiSource={false}
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
        validationErrors={filterErrors(validationErrors, '[operations]')}
      />
    </>
  );
};

export {DateConfigurator};
