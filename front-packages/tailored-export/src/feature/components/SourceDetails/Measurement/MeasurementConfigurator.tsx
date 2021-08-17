import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {AttributeConfiguratorProps} from '../../../models';
import {isMeasurementSource} from './model';
import {MeasurementSelector} from './MeasurementSelector';
import {InvalidAttributeSourceError} from '../error';
import {DefaultValue, Operations} from '../common';

const MeasurementConfigurator = ({source, validationErrors, onSourceChange}: AttributeConfiguratorProps) => {
  if (!isMeasurementSource(source)) {
    throw new InvalidAttributeSourceError(`Invalid source data "${source.code}" for measurement configurator`);
  }

  return (
    <Operations>
      <DefaultValue
        operation={source.operations.default_value}
        validationErrors={filterErrors(validationErrors, '[operations][default_value]')}
        onOperationChange={updatedOperation =>
          onSourceChange({...source, operations: {...source.operations, default_value: updatedOperation}})
        }
      />
      <MeasurementSelector
        selection={source.selection}
        validationErrors={filterErrors(validationErrors, '[selection]')}
        onSelectionChange={updatedMeasurementSelection =>
          onSourceChange({...source, selection: updatedMeasurementSelection})
        }
      />
    </Operations>
  );
};

export {MeasurementConfigurator};
