import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {AttributeConfiguratorProps} from '../../../models';
import {isFileSource} from './model';
import {FileSelector} from './FileSelector';
import {InvalidAttributeSourceError} from '../error';
import {DefaultValue, Operations} from '../common';

const FileConfigurator = ({source, validationErrors, onSourceChange}: AttributeConfiguratorProps) => {
  if (!isFileSource(source)) {
    throw new InvalidAttributeSourceError(`Invalid source data "${source.code}" for file configurator`);
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
      <FileSelector
        selection={source.selection}
        validationErrors={filterErrors(validationErrors, '[selection]')}
        onSelectionChange={updatedFileSelection => onSourceChange({...source, selection: updatedFileSelection})}
      />
    </Operations>
  );
};

export {FileConfigurator};
