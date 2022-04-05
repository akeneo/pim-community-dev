import React from 'react';
import {Helper} from 'akeneo-design-system';
import {filterErrors, useTranslate} from '@akeneo-pim-community/shared';
import {AttributeDataMappingConfiguratorProps} from '../../../../models';
import {AttributeTargetParameters, Operations, Sources} from '../../../../components';

const IdentifierConfigurator = ({
  attribute,
  columns,
  dataMapping,
  onSourcesChange,
  onRefreshSampleData,
  onTargetChange,
  validationErrors,
}: AttributeDataMappingConfiguratorProps) => {
  const translate = useTranslate();

  return (
    <>
      <AttributeTargetParameters
        attribute={attribute}
        target={dataMapping.target}
        validationErrors={filterErrors(validationErrors, '[target]')}
        onTargetChange={onTargetChange}
      >
        <Helper>{translate('akeneo.tailored_import.data_mapping.target.identifier')}</Helper>
      </AttributeTargetParameters>
      <Sources
        sources={dataMapping.sources}
        columns={columns}
        validationErrors={filterErrors(validationErrors, '[sources]')}
        onSourcesChange={onSourcesChange}
      />
      <Operations dataMapping={dataMapping} onRefreshSampleData={onRefreshSampleData} />
    </>
  );
};

export {IdentifierConfigurator};
