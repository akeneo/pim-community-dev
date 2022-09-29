import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {AttributeDataMappingConfiguratorProps, AttributeTarget} from '../../../../models';
import {InvalidAttributeTargetError} from '../error';
import {AttributeTargetParameters} from '../../AttributeTargetParameters';
import {ActionIfNotEmpty, ClearIfEmpty} from '../../common';
import {Sources} from '../../Sources';
import {Operations} from '../../Operations';
import {SPLIT_OPERATION_TYPE, MULTI_REFERENCE_ENTITY_REPLACEMENT_OPERATION_TYPE} from '../../Operation';
import {isMultiReferenceEntityTarget} from './model';

const MultiReferenceEntityConfigurator = ({
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

  if (!isMultiReferenceEntityTarget(target)) {
    throw new InvalidAttributeTargetError(
      `Invalid target data "${target.code}" for multi reference entity configurator`
    );
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
        compatibleOperations={[SPLIT_OPERATION_TYPE, MULTI_REFERENCE_ENTITY_REPLACEMENT_OPERATION_TYPE]}
        onOperationsChange={onOperationsChange}
        onRefreshSampleData={onRefreshSampleData}
        validationErrors={filterErrors(validationErrors, '[operations]')}
      />
    </>
  );
};

export {MultiReferenceEntityConfigurator};
