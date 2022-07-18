import React, {FunctionComponent} from 'react';
import {Helper} from 'akeneo-design-system';
import {getErrorsForPath, useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {
  AttributeDataMapping,
  AttributeDataMappingConfiguratorProps,
  AttributeTarget,
  Column,
  ColumnIdentifier,
  Operation,
} from '../../models';
import {useAttribute} from '../../hooks';
import {
  IdentifierConfigurator,
  MeasurementConfigurator,
  NumberConfigurator,
  SimpleReferenceEntityConfigurator,
  SimpleSelectConfigurator,
  MultiSelectConfigurator,
  BooleanConfigurator,
  TextConfigurator,
  DateConfigurator,
  AssetCollectionConfigurator,
} from './Attribute';
import {AttributeDoesNotExist} from './AttributeDoesNotExist';
import {ErrorBoundary} from './ErrorBoundary';

const attributeDataMappingConfigurators: {
  [attributeType: string]: FunctionComponent<AttributeDataMappingConfiguratorProps>;
} = {
  akeneo_reference_entity: SimpleReferenceEntityConfigurator,
  pim_catalog_date: DateConfigurator,
  pim_catalog_identifier: IdentifierConfigurator,
  pim_catalog_metric: MeasurementConfigurator,
  pim_catalog_number: NumberConfigurator,
  pim_catalog_text: TextConfigurator,
  pim_catalog_textarea: TextConfigurator,
  pim_catalog_simpleselect: SimpleSelectConfigurator,
  pim_catalog_multiselect: MultiSelectConfigurator,
  pim_catalog_boolean: BooleanConfigurator,
  pim_catalog_asset_collection: AssetCollectionConfigurator,
};

type AttributeDataMappingDetailsProps = {
  columns: Column[];
  dataMapping: AttributeDataMapping;
  validationErrors: ValidationError[];
  onOperationsChange: (operations: Operation[]) => void;
  onRefreshSampleData: (index: number) => Promise<void>;
  onSourcesChange: (sources: ColumnIdentifier[]) => void;
  onTargetChange: (target: AttributeTarget) => void;
};

const AttributeDataMappingDetails = ({
  columns,
  dataMapping,
  validationErrors,
  onOperationsChange,
  onRefreshSampleData,
  onSourcesChange,
  onTargetChange,
}: AttributeDataMappingDetailsProps) => {
  const attributeErrors = getErrorsForPath(validationErrors, '[target][code]');
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
        <AttributeDoesNotExist />
      </>
    );
  }

  const Configurator = attributeDataMappingConfigurators[attribute.type] ?? null;

  if (null === Configurator) {
    console.error(`No configurator found for "${attribute.type}" attribute type`);

    return null;
  }

  return (
    <ErrorBoundary>
      <Configurator
        dataMapping={dataMapping}
        attribute={attribute}
        columns={columns}
        validationErrors={validationErrors}
        onOperationsChange={onOperationsChange}
        onRefreshSampleData={onRefreshSampleData}
        onSourcesChange={onSourcesChange}
        onTargetChange={onTargetChange}
      />
    </ErrorBoundary>
  );
};

export {AttributeDataMappingDetails};
