import React from 'react';
import {Helper, SectionTitle} from 'akeneo-design-system';
import {filterErrors} from '@akeneo-pim-community/shared';
import {AttributeDataMappingConfiguratorProps} from '../../../../models';
import {Sources} from '../../../../components';
import {useTranslate} from '@akeneo-pim-community/shared';

const IdentifierConfigurator = ({
  columns,
  dataMapping,
  onSourcesChange,
  validationErrors,
}: AttributeDataMappingConfiguratorProps) => {
  const translate = useTranslate();
  return (
    <>
      <div>
        <SectionTitle sticky={0}>
          <SectionTitle.Title level="secondary">
            {translate('akeneo.tailored_import.data_mapping.target.title')}
          </SectionTitle.Title>
        </SectionTitle>
        <Helper level="info">{translate('akeneo.tailored_import.data_mapping.target.identifier')}</Helper>
      </div>
      <Sources
        isMultiSource={false}
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
        <Helper level="info">{translate('akeneo.tailored_import.data_mapping.operations.identifier')}</Helper>
      </div>
    </>
  );
};

export {IdentifierConfigurator};
