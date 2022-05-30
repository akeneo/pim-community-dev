import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {isNumberTarget, NumberSourceConfiguration} from './model';
import {AttributeDataMappingConfiguratorProps, AttributeTarget} from '../../../../models';
import {InvalidAttributeTargetError} from '../error/InvalidAttributeTargetError';
import {AttributeTargetParameters, Operations, Sources} from '../../../../components';
import {DecimalSeparatorField} from '../../common/DecimalSeparatorField';
import {ClearIfEmpty} from '../../common/ClearIfEmpty';

const NumberConfigurator = ({
  dataMapping,
  attribute,
  columns,
  validationErrors,
  onOperationsChange,
  onRefreshSampleData,
  onSourcesChange,
  onTargetChange,
}: AttributeDataMappingConfiguratorProps) => {
  const target = dataMapping.target;
  const decimalSeparatorErrors = filterErrors(validationErrors, '[target][decimal_separator]');
  if (!isNumberTarget(target)) {
    throw new InvalidAttributeTargetError(`Invalid target data "${target.code}" for number configurator`);
  }

  const handleSourceConfigurationChange = (updatedSourceConfiguration: NumberSourceConfiguration) =>
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
      />
    </>
  );
};

export {NumberConfigurator};
