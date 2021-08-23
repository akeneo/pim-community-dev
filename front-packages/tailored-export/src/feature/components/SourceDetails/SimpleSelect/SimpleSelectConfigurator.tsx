import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {AttributeConfiguratorProps} from '../../../models';
import {CodeLabelSelector, DefaultValue, Operations} from '../common';
import {isSimpleSelectSource} from './model';
import {InvalidAttributeSourceError} from '../error';

const SimpleSelectConfigurator = ({source, validationErrors, onSourceChange}: AttributeConfiguratorProps) => {
  if (!isSimpleSelectSource(source)) {
    throw new InvalidAttributeSourceError(`Invalid source data "${source.code}" for simple select configurator`);
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
      <CodeLabelSelector
        selection={source.selection}
        validationErrors={filterErrors(validationErrors, '[selection]')}
        onSelectionChange={updatedSelection => onSourceChange({...source, selection: updatedSelection})}
      />
    </Operations>
  );
};

export {SimpleSelectConfigurator};
