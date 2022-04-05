import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {isTextTarget} from './model';
import {AttributeDataMappingConfiguratorProps} from '../../../../models';
import {InvalidAttributeTargetError} from '../error/InvalidAttributeTargetError';
import {AttributeTargetParameters, Operations, Sources} from '../../../../components';
import {CLEAN_HTML_TAGS_TYPE} from '../../Operation';
import {ClearIfEmpty} from "../../common/ClearIfEmpty";

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
        <ClearIfEmpty target={target} onTargetChange={onTargetChange} />
      </AttributeTargetParameters>
      <Sources
        sources={dataMapping.sources}
        columns={columns}
        validationErrors={filterErrors(validationErrors, '[sources]')}
        onSourcesChange={onSourcesChange}
      />
      <Operations
        dataMapping={dataMapping}
        compatibleOperations={[CLEAN_HTML_TAGS_TYPE]}
        onOperationsChange={onOperationsChange}
        onRefreshSampleData={onRefreshSampleData}
      />
    </>
  );
};

export {TextConfigurator};
