import React from 'react';
import {Helper, SectionTitle} from 'akeneo-design-system';
import {filterErrors, useTranslate} from '@akeneo-pim-community/shared';
import {AttributeDataMappingConfiguratorProps} from '../../../../models';
import {AttributeTargetParameters, Sources} from '../../../../components';

const IdentifierConfigurator = ({
  attribute,
  columns,
  dataMapping,
  onSourcesChange,
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
      <div>
        <SectionTitle sticky={0}>
          <SectionTitle.Title level="secondary">
            {translate('akeneo.tailored_import.data_mapping.operations.title')}
          </SectionTitle.Title>
        </SectionTitle>
        <Helper>{translate('akeneo.tailored_import.data_mapping.operations.identifier')}</Helper>
      </div>
    </>
  );
};

export {IdentifierConfigurator};
