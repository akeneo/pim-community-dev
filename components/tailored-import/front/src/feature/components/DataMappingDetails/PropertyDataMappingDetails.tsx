import React, {FunctionComponent} from 'react';
import {getErrorsForPath, useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {
  Column,
  ColumnIdentifier,
  Operation,
  PropertyDataMappingConfiguratorProps,
  PropertyDataMapping,
  PropertyTarget,
} from '../../models';
import {CategoriesConfigurator} from './Property';
import {Helper} from 'akeneo-design-system';
import {PropertyNotValid} from './PropertyNotValid';

const propertyDataMappingConfigurators: {
  [propertyCode: string]: FunctionComponent<PropertyDataMappingConfiguratorProps>;
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
  const translate = useTranslate();
  const codeErrors = getErrorsForPath(validationErrors, '[target][code]');
  const Configurator = propertyDataMappingConfigurators[dataMapping.target.code] ?? null;

  if (0 < codeErrors.length || null === Configurator) {
    return (
      <>
        {codeErrors.map((error, index) => (
          <Helper key={index} level="error">
            {translate(error.messageTemplate, error.parameters)}
          </Helper>
        ))}
        <PropertyNotValid />
      </>
    );
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
