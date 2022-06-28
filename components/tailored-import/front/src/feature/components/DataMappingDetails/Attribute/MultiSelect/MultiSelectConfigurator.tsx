import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {isMultiSelectTarget} from './model';
import {AttributeDataMappingConfiguratorProps, AttributeTarget} from '../../../../models';
import {InvalidAttributeTargetError} from '../error/InvalidAttributeTargetError';
import {AttributeTargetParameters, Operations, Sources, ClearIfEmpty, ActionIfNotEmpty} from '../../../../components';
import {MULTI_SELECT_REPLACEMENT_OPERATION_TYPE, SPLIT_OPERATION_TYPE} from '../../Operation';

const MultiSelectConfigurator = ({
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

  if (!isMultiSelectTarget(target)) {
    throw new InvalidAttributeTargetError(`Invalid target data "${target.code}" for multi select configurator`);
  }

  return (
    <>
      <AttributeTargetParameters
        attribute={attribute}
        target={dataMapping.target}
        validationErrors={filterErrors(validationErrors, '[target]')}
        onTargetChange={onTargetChange}
      >
        <ActionIfNotEmpty<AttributeTarget> target={target} onTargetChange={onTargetChange} />
        <ClearIfEmpty<AttributeTarget> target={target} onTargetChange={onTargetChange} />
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
        compatibleOperations={[SPLIT_OPERATION_TYPE, MULTI_SELECT_REPLACEMENT_OPERATION_TYPE]}
        onOperationsChange={onOperationsChange}
        onRefreshSampleData={onRefreshSampleData}
        validationErrors={filterErrors(validationErrors, '[operations]')}
      />
    </>
  );
};

export {MultiSelectConfigurator};
