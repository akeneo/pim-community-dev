import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {isCategoriesTarget} from './model';
import {PropertyDataMappingConfiguratorProps, PropertyTarget} from '../../../../models';
import {InvalidPropertyTargetError} from '../error/InvalidPropertyTargetError';
import {PropertyTargetParameters, Operations, Sources, ActionIfNotEmpty, ClearIfEmpty} from '../../../../components';
import {SPLIT_OPERATION_TYPE} from '../../Operation';
import {CATEGORIES_REPLACEMENT_OPERATION_TYPE} from '../../Operation/Block/CategoriesReplacementOperationBlock';

const CategoriesConfigurator = ({
  dataMapping,
  columns,
  validationErrors,
  onOperationsChange,
  onRefreshSampleData,
  onSourcesChange,
  onTargetChange,
}: PropertyDataMappingConfiguratorProps) => {
  const target = dataMapping.target;

  if (!isCategoriesTarget(target)) {
    throw new InvalidPropertyTargetError(`Invalid target data "${target.code}" for categories configurator`);
  }

  return (
    <>
      <PropertyTargetParameters target={dataMapping.target} onTargetChange={onTargetChange}>
        <ClearIfEmpty<PropertyTarget> target={target} onTargetChange={onTargetChange} />
        <ActionIfNotEmpty<PropertyTarget> target={target} onTargetChange={onTargetChange} />
      </PropertyTargetParameters>
      <Sources
        isMultiSource={true}
        sources={dataMapping.sources}
        columns={columns}
        validationErrors={filterErrors(validationErrors, '[sources]')}
        onSourcesChange={onSourcesChange}
      />
      <Operations
        dataMapping={dataMapping}
        compatibleOperations={[SPLIT_OPERATION_TYPE, CATEGORIES_REPLACEMENT_OPERATION_TYPE]}
        onOperationsChange={onOperationsChange}
        onRefreshSampleData={onRefreshSampleData}
        validationErrors={filterErrors(validationErrors, '[operations]')}
      />
    </>
  );
};

export {CategoriesConfigurator};
