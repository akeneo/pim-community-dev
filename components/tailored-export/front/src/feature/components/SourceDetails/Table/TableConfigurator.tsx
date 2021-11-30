import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {AttributeConfiguratorProps} from '../../../models';
import {DefaultValue, Operations} from '../common';
import {InvalidAttributeSourceError} from '../error';
import {isTableSource} from './model';

const TableConfigurator = ({source, validationErrors, onSourceChange}: AttributeConfiguratorProps) => {
  if (!isTableSource(source)) {
    throw new InvalidAttributeSourceError(`Invalid source data "${source.code}" for Table configurator`);
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
    </Operations>
  );
};

export {TableConfigurator};
