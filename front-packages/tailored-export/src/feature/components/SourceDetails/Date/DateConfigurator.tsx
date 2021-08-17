import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {AttributeConfiguratorProps} from '../../../models';
import {DateSelector} from './DateSelector';
import {isDateSource} from './model';
import {InvalidAttributeSourceError} from '../error';
import {DefaultValue, Operations} from '../common';

const DateConfigurator = ({source, validationErrors, onSourceChange}: AttributeConfiguratorProps) => {
  if (!isDateSource(source)) {
    throw new InvalidAttributeSourceError(`Invalid source data "${source.code}" for date configurator`);
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
      <DateSelector
        selection={source.selection}
        validationErrors={filterErrors(validationErrors, '[selection]')}
        onSelectionChange={updatedSelection => onSourceChange({...source, selection: updatedSelection})}
      />
    </Operations>
  );
};

export {DateConfigurator};
