import React, {FunctionComponent} from 'react';
import {ValidationError} from '@akeneo-pim-community/shared';
import {
  Column,
  ColumnIdentifier,
  Operation,
  PropertyDaraMappingConfiguratorProps,
  PropertyDataMapping,
  PropertyTarget,
} from '../../models';
import {CategoriesConfigurator} from './Property';

const propertyDataMappingConfigurators: {
  [propertyCode: string]: FunctionComponent<PropertyDaraMappingConfiguratorProps>;
} = {
  categories: CategoriesConfigurator,
};

type PropertyDataMappingDetailsProps = {
  columns: Column[];
  dataMapping: PropertyDataMapping;
  validationErrors: ValidationError[];
  onOperationsChange: (operations: Operation[]) => void;
  onRefreshSampleData: (index: number) => Promise<void>;
  onSourcesChange: (sources: ColumnIdentifier[]) => void;
  onTargetChange: (target: PropertyTarget) => void;
};

const PropertyDataMappingDetails = ({
  columns,
  dataMapping,
  validationErrors,
  onOperationsChange,
  onRefreshSampleData,
  onSourcesChange,
  onTargetChange,
}: PropertyDataMappingDetailsProps) => {
  const Configurator = propertyDataMappingConfigurators[dataMapping.target.code] ?? null;

  if (null === Configurator) {
    console.error(`No configurator found for "${dataMapping.target.code}" property`);

    return null;
  }

  return (
    <Configurator
      dataMapping={dataMapping}
      columns={columns}
      validationErrors={validationErrors}
      onOperationsChange={onOperationsChange}
      onRefreshSampleData={onRefreshSampleData}
      onSourcesChange={onSourcesChange}
      onTargetChange={onTargetChange}
    />
  );
};

export {PropertyDataMappingDetails};
