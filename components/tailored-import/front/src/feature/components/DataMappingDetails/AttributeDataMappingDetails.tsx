import React, {FunctionComponent} from 'react';
import {Helper} from 'akeneo-design-system';
import {getErrorsForPath, useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {
  AttributeDataMapping,
  AttributeDataMappingConfiguratorProps,
  AttributeTarget,
  Column,
  ColumnIdentifier,
} from '../../models';
import {useAttribute} from '../../hooks';
import {IdentifierConfigurator, MeasurementConfigurator, NumberConfigurator, TextConfigurator} from './Attribute';

const attributeDataMappingConfigurators: {
  [attributeType: string]: FunctionComponent<AttributeDataMappingConfiguratorProps>;
} = {
  pim_catalog_identifier: IdentifierConfigurator,
  pim_catalog_metric: MeasurementConfigurator,
  pim_catalog_number: NumberConfigurator,
  pim_catalog_text: TextConfigurator,
  pim_catalog_textarea: TextConfigurator,
};

type AttributeDataMappingDetailsProps = {
  columns: Column[];
  dataMapping: AttributeDataMapping;
  validationErrors: ValidationError[];
  onRefreshSampleData: (index: number) => Promise<void>;
  onSourcesChange: (sources: ColumnIdentifier[]) => void;
  onTargetChange: (target: AttributeTarget) => void;
};

const AttributeDataMappingDetails = ({
  columns,
  dataMapping,
  onTargetChange,
  onRefreshSampleData,
  onSourcesChange,
  validationErrors,
}: AttributeDataMappingDetailsProps) => {
  const attributeErrors = getErrorsForPath(validationErrors, '');
  const translate = useTranslate();
  const [isFetching, attribute] = useAttribute(dataMapping.target.code);

  if (isFetching) return null;

  if (null === attribute) {
    return (
      <>
        {attributeErrors.map((error, index) => (
          <Helper key={index} level="error">
            {translate(error.messageTemplate, error.parameters)}
          </Helper>
        ))}
      </>
    );
  }

  const Configurator = attributeDataMappingConfigurators[attribute.type] ?? null;

  if (null === Configurator) {
    console.error(`No configurator found for "${attribute.type}" attribute type`);

    return null;
  }

  return (
    <Configurator
      dataMapping={dataMapping}
      attribute={attribute}
      columns={columns}
      validationErrors={validationErrors}
      onRefreshSampleData={onRefreshSampleData}
      onSourcesChange={onSourcesChange}
      onTargetChange={onTargetChange}
    />
  );
};

export {AttributeDataMappingDetails};
