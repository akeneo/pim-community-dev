import React, {FunctionComponent} from 'react';
import {ValidationError} from '@akeneo-pim-community/shared';
import {StaticConfiguratorProps, StaticSource} from '../../models';
import {ErrorBoundary} from './error';
import {Requirement} from '../../models';
import {BooleanConfigurator, StringConfigurator, MeasurementConfigurator} from './Static';

const configurators: {[propertyName: string]: FunctionComponent<StaticConfiguratorProps>} = {
  boolean: BooleanConfigurator,
  string: StringConfigurator,
  measurement: MeasurementConfigurator,
};

type StaticSourceConfiguratorProps = {
  source: StaticSource;
  requirement: Requirement;
  validationErrors: ValidationError[];
  onSourceChange: (updatedSource: StaticSource) => void;
};

const StaticSourceConfigurator = ({
  source,
  requirement,
  validationErrors,
  onSourceChange,
}: StaticSourceConfiguratorProps) => {
  const Configurator = configurators[source.code] ?? null;

  if (null === Configurator) {
    console.error(`No configurator found for "${source.code}" source type`);

    return null;
  }

  return (
    <ErrorBoundary>
      <Configurator
        source={source}
        validationErrors={validationErrors}
        onSourceChange={onSourceChange}
        requirement={requirement}
      />
    </ErrorBoundary>
  );
};

export {StaticSourceConfigurator};
