import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {isNumberTarget, NumberSourceParameter} from './model';
import {AttributeDataMappingConfiguratorProps} from '../../../../models';
import {InvalidAttributeTargetError} from '../error/InvalidAttributeTargetError';
import {AttributeTargetParameters, Operations, Sources} from '../../../../components';
import {DecimalSeparatorField} from '../../common/DecimalSeparatorField';
import {ClearIfEmpty} from "../../common/ClearIfEmpty";

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

  const handleSourceParameterChange = (updatedSourceParameter: NumberSourceParameter) =>
    onTargetChange({...target, source_parameter: updatedSourceParameter});

  return (
    <>
      <AttributeTargetParameters
        attribute={attribute}
        target={dataMapping.target}
        validationErrors={filterErrors(validationErrors, '[target]')}
        onTargetChange={onTargetChange}
      >
        <ClearIfEmpty target={target} onTargetChange={onTargetChange} />
        {attribute.decimals_allowed && (
          <DecimalSeparatorField
            value={target.source_parameter.decimal_separator}
            onChange={decimalSeparator =>
              handleSourceParameterChange({...target.source_parameter, decimal_separator: decimalSeparator})
            }
            validationErrors={decimalSeparatorErrors}
          />
        )}
      </AttributeTargetParameters>
      <Sources
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
