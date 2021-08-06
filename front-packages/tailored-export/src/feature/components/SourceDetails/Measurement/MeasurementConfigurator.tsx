import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {AttributeConfiguratorProps} from '../../../models';
import {isMeasurementSource} from './model';
import {MeasurementSelector} from './MeasurementSelector';
import {InvalidAttributeSourceError} from '../error';

const MeasurementConfigurator = ({source, validationErrors, onSourceChange}: AttributeConfiguratorProps) => {
  if (!isMeasurementSource(source)) {
    throw new InvalidAttributeSourceError(`Invalid source data "${source.code}" for measurement configurator`);
  }

  return (
    <MeasurementSelector
      selection={source.selection}
      validationErrors={filterErrors(validationErrors, '[selection]')}
      onSelectionChange={updatedMeasurementSelection =>
        onSourceChange({...source, selection: updatedMeasurementSelection})
      }
    />
  );
};

export {MeasurementConfigurator};
