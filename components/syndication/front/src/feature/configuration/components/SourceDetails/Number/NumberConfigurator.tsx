import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {isNumberSource} from './model';
import {AttributeConfiguratorProps} from '../../../models';
import {NumberSelector} from './NumberSelector';
import {InvalidAttributeSourceError} from '../error';
import {DefaultValue, Operations} from '../common';
import {NoOperationsPlaceholder} from '../NoOperationsPlaceholder';

const NumberConfigurator = ({source, requirement, onSourceChange, validationErrors}: AttributeConfiguratorProps) => {
  if (!isNumberSource(source)) {
    throw new InvalidAttributeSourceError(`Invalid source data "${source.code}" for number configurator`);
  }

  // TODO: we should add back the DefaultValue component when we have a proper implementation
  if ('string' !== requirement.type) return <NoOperationsPlaceholder />;

  return (
    <Operations>
      <DefaultValue
        operation={source.operations.default_value}
        validationErrors={filterErrors(validationErrors, '[operations][default_value]')}
        onOperationChange={updatedOperation =>
          onSourceChange({...source, operations: {...source.operations, default_value: updatedOperation}})
        }
      />
      <NumberSelector
        selection={source.selection}
        validationErrors={filterErrors(validationErrors, '[selection]')}
        onSelectionChange={updatedNumberSelection => onSourceChange({...source, selection: updatedNumberSelection})}
      />
    </Operations>
  );
};

export {NumberConfigurator};
