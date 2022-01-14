import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {PropertyConfiguratorProps} from '../../../models';
import {CodeLabelSelector, DefaultValue, Extract, Operations} from '../common';
import {isFamilySource} from './model';
import {InvalidPropertySourceError} from '../error';

const FamilyConfigurator = ({source, validationErrors, onSourceChange}: PropertyConfiguratorProps) => {
  if (!isFamilySource(source)) {
    throw new InvalidPropertySourceError(`Invalid source data "${source.code}" for family configurator`);
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
      <Extract
        operation={source.operations.extract}
        validationErrors={filterErrors(validationErrors, '[operations][extract]')}
        onOperationChange={updatedOperation =>
          onSourceChange({...source, operations: {...source.operations, extract: updatedOperation}})
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

export {FamilyConfigurator};
