import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {isTextTarget} from './model';
import {AttributeDataMappingConfiguratorProps, AttributeTarget} from '../../../../models';
import {InvalidAttributeTargetError} from '../error';
import {AttributeTargetParameters, Operations, Sources} from '../../../../components';
import {
  CHANGE_CASE_OPERATION_TYPE,
  CLEAN_HTML_OPERATION_TYPE,
  REMOVE_WHITESPACE_OPERATION_TYPE,
  SEARCH_AND_REPLACE_OPERATION_TYPE,
} from '../../Operation';
import {ClearIfEmpty} from '../../common';

const TextConfigurator = ({
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

  if (!isTextTarget(target)) {
    throw new InvalidAttributeTargetError(`Invalid target data "${target.code}" for text configurator`);
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
        compatibleOperations={[
          CLEAN_HTML_OPERATION_TYPE,
          CHANGE_CASE_OPERATION_TYPE,
          REMOVE_WHITESPACE_OPERATION_TYPE,
          SEARCH_AND_REPLACE_OPERATION_TYPE,
        ]}
        onOperationsChange={onOperationsChange}
        onRefreshSampleData={onRefreshSampleData}
        validationErrors={filterErrors(validationErrors, '[operations]')}
      />
    </>
  );
};

export {TextConfigurator};
