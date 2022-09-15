import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {isSimpleReferenceEntityTarget} from './model';
import {AttributeDataMappingConfiguratorProps, AttributeTarget} from '../../../../models';
import {InvalidAttributeTargetError} from '../error/InvalidAttributeTargetError';
import {AttributeTargetParameters, Operations, Sources, ClearIfEmpty} from '../../../../components';
import {REFERENCE_ENTITY_SINGLE_LINK_REPLACEMENT} from "../../Operation";

const SimpleReferenceEntityConfigurator = ({
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

  if (!isSimpleReferenceEntityTarget(target)) {
    throw new InvalidAttributeTargetError(
      `Invalid target data "${target.code}" for simple reference entity configurator`
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
        <ClearIfEmpty<AttributeTarget> target={target} onTargetChange={onTargetChange} />
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
        compatibleOperations={[REFERENCE_ENTITY_SINGLE_LINK_REPLACEMENT]}
        onOperationsChange={onOperationsChange}
        onRefreshSampleData={onRefreshSampleData}
        validationErrors={filterErrors(validationErrors, '[operations]')}
      />
    </>
  );
};

export {SimpleReferenceEntityConfigurator};
