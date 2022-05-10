import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {isCategoryTarget} from './model';
import {PropertyDaraMappingConfiguratorProps} from '../../../../models';
import {InvalidPropertyTargetError} from '../error/InvalidPropertyTargetError';
import {PropertyTargetParameters, Operations, Sources} from '../../../../components';

const CategoriesConfigurator = ({
  dataMapping,
  columns,
  validationErrors,
  onOperationsChange,
  onRefreshSampleData,
  onSourcesChange,
  onTargetChange,
}: PropertyDaraMappingConfiguratorProps) => {
  const target = dataMapping.target;

  if (!isCategoryTarget(target)) {
    throw new InvalidPropertyTargetError(`Invalid target data "${target.code}" for category configurator`);
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
        compatibleOperations={[]}
        onOperationsChange={onOperationsChange}
        onRefreshSampleData={onRefreshSampleData}
      />
    </>
  );
};

export {CategoriesConfigurator};
