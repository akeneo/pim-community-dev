import React from 'react';
import {Helper, SectionTitle} from 'akeneo-design-system';
import {filterErrors, useTranslate} from '@akeneo-pim-community/shared';
import {isFamilyTarget} from './model';
import {PropertyDataMappingConfiguratorProps} from '../../../../models';
import {InvalidPropertyTargetError} from '../error/InvalidPropertyTargetError';
import {Operations, Sources} from '../../../../components';

const FamilyConfigurator = ({
  dataMapping,
  columns,
  validationErrors,
  onOperationsChange,
  onRefreshSampleData,
  onSourcesChange,
}: PropertyDataMappingConfiguratorProps) => {
  const target = dataMapping.target;
  const translate = useTranslate();

  if (!isFamilyTarget(target)) {
    throw new InvalidPropertyTargetError(`Invalid target data "${target.code}" for family configurator`);
  }

  return (
    <>
      <div>
        <SectionTitle sticky={0}>
          <SectionTitle.Title level="secondary">
            {translate('akeneo.tailored_import.data_mapping.target.title')}
          </SectionTitle.Title>
        </SectionTitle>
        <Helper level="info">{translate('akeneo.tailored_import.data_mapping.target.family')}</Helper>
      </div>
      <Sources
        isMultiSource={false}
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

export {FamilyConfigurator};
