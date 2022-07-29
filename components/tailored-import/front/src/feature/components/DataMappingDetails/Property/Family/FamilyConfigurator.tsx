import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {isFamilyTarget} from './model';
import {PropertyDataMappingConfiguratorProps, PropertyTarget} from '../../../../models';
import {InvalidPropertyTargetError} from '../error/InvalidPropertyTargetError';
import {ClearIfEmpty, Operations, PropertyTargetParameters, Sources} from '../../../../components';
import {FAMILY_REPLACEMENT_OPERATION_TYPE} from "../../Operation";

const FamilyConfigurator = ({
  dataMapping,
  columns,
  validationErrors,
  onOperationsChange,
  onRefreshSampleData,
  onSourcesChange,
  onTargetChange,
}: PropertyDataMappingConfiguratorProps) => {
  const target = dataMapping.target;

  if (!isFamilyTarget(target)) {
    throw new InvalidPropertyTargetError(`Invalid target data "${target.code}" for family configurator`);
  }

  return (
    <>
      <PropertyTargetParameters target={dataMapping.target} onTargetChange={onTargetChange}>
        <ClearIfEmpty<PropertyTarget> target={target} onTargetChange={onTargetChange} />
      </PropertyTargetParameters>
      <Sources
        isMultiSource={false}
        sources={dataMapping.sources}
        columns={columns}
        validationErrors={filterErrors(validationErrors, '[sources]')}
        onSourcesChange={onSourcesChange}
      />
      <Operations
        dataMapping={dataMapping}
        compatibleOperations={[FAMILY_REPLACEMENT_OPERATION_TYPE]}
        onOperationsChange={onOperationsChange}
        onRefreshSampleData={onRefreshSampleData}
        validationErrors={filterErrors(validationErrors, '[operations]')}
      />
    </>
  );
};

export {FamilyConfigurator};
