import React from 'react';
import {Checkbox} from 'akeneo-design-system';
import {filterErrors, useTranslate} from '@akeneo-pim-community/shared';
import {isTextTarget} from './model';
import {AttributeDataMappingConfiguratorProps} from '../../../../models';
import {InvalidAttributeTargetError} from '../error/InvalidAttributeTargetError';
import {AttributeTargetParameters, Operations, Sources} from '../../../../components';
import {CLEAN_HTML_TAGS_TYPE} from '../../Operation';

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

  const translate = useTranslate();

  const handleClearIfEmptyChange = (clearIfEmpty: boolean) =>
    onTargetChange({...target, action_if_empty: clearIfEmpty ? 'clear' : 'skip'});

  return (
    <>
      <AttributeTargetParameters
        attribute={attribute}
        target={dataMapping.target}
        validationErrors={filterErrors(validationErrors, '[target]')}
        onTargetChange={onTargetChange}
      >
        <Checkbox checked={'clear' === target.action_if_empty} onChange={handleClearIfEmptyChange}>
          {translate('akeneo.tailored_import.data_mapping.target.clear_if_empty')}
        </Checkbox>
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
