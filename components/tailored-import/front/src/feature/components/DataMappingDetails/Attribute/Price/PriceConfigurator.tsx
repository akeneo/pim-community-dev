import React from 'react';
import {filterErrors, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {AttributeDataMappingConfiguratorProps, AttributeTarget} from '../../../../models';
import {isPriceTarget, PriceSourceConfiguration} from './model';
import {InvalidAttributeTargetError} from '../error/InvalidAttributeTargetError';
import {AttributeTargetParameters, ClearIfEmpty, DecimalSeparatorField, Operations, Sources} from '../../..';
import {CurrencySelector} from './CurrencySelector';

const PriceConfigurator = ({
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
  if (!isPriceTarget(target)) {
    throw new InvalidAttributeTargetError(`Invalid target data "${dataMapping.target.code}" for price configurator`);
  }

  const decimalSeparatorErrors = filterErrors(validationErrors, '[target][decimal_separator]');
  const currencyErrors = filterErrors(validationErrors, '[target][currency]');

  const handleSourceConfigurationChange = (sourceConfiguration: PriceSourceConfiguration) => {
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
        <ClearIfEmpty<AttributeTarget> target={target} onTargetChange={onTargetChange} />
        <CurrencySelector
          value={target.source_configuration.currency}
          channelReference={target.channel}
          onChange={currency => handleSourceConfigurationChange({...target.source_configuration, currency: currency})}
          validationErrors={currencyErrors}
        />
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

export {PriceConfigurator};
