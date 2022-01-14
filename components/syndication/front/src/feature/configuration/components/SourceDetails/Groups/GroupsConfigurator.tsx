import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {PropertyConfiguratorProps} from '../../../models';
import {CodeLabelCollectionSelector, DefaultValue, Operations} from '../common';
import {isGroupsSource} from './model';
import {InvalidPropertySourceError} from '../error';

const GroupsConfigurator = ({source, validationErrors, onSourceChange}: PropertyConfiguratorProps) => {
  if (!isGroupsSource(source)) {
    throw new InvalidPropertySourceError(`Invalid source data "${source.code}" for groups configurator`);
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
      <CodeLabelCollectionSelector
        selection={source.selection}
        validationErrors={filterErrors(validationErrors, '[selection]')}
        onSelectionChange={updatedSelection => onSourceChange({...source, selection: updatedSelection})}
      />
    </Operations>
  );
};

export {GroupsConfigurator};
