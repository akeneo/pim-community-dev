import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {StaticConfiguratorProps} from '../../../../models';
import {isStaticMeasurementSource} from './model';
import {InvalidAttributeSourceError} from '../../error';
import {MeasurementValueGeneratorConfigurator} from './MeasurementValueGenerator';
import styled from 'styled-components';

const Container = styled.div`
  padding-top: 10px;
`;

const MeasurementConfigurator = ({source, requirement, validationErrors, onSourceChange}: StaticConfiguratorProps) => {
  if (!isStaticMeasurementSource(source)) {
    throw new InvalidAttributeSourceError(`Invalid source data "${source.code}" for string configurator`);
  }

  if (undefined === requirement?.options?.measurementFamily) {
    throw new Error('Missing measurement family in requirement');
  }

  return (
    <Container>
      <MeasurementValueGeneratorConfigurator
        value={source.value}
        measurementFamilyCode={requirement.options.measurementFamily}
        validationErrors={filterErrors(validationErrors, '[value]')}
        onValueChange={newValue => onSourceChange({...source, value: newValue})}
      />
    </Container>
  );
};

export {MeasurementConfigurator};
