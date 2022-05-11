import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {isCategoriesTarget} from './model';
import {PropertyDataMappingConfiguratorProps} from '../../../../models';
import {InvalidPropertyTargetError} from '../error/InvalidPropertyTargetError';
import {PropertyTargetParameters, Operations, Sources} from '../../../../components';
import {SPLIT_OPERATION_TYPE} from '../../Operation';

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
      <PropertyTargetParameters target={dataMapping.target} onTargetChange={onTargetChange} />
      <Sources
        isMultiSource={true}
        sources={dataMapping.sources}
        columns={columns}
        validationErrors={filterErrors(validationErrors, '[sources]')}
        onSourcesChange={onSourcesChange}
      />
      <Operations
        dataMapping={dataMapping}
        compatibleOperations={[SPLIT_OPERATION_TYPE]}
        onOperationsChange={onOperationsChange}
        onRefreshSampleData={onRefreshSampleData}
      />
    </>
  );
};

export {CategoriesConfigurator};
