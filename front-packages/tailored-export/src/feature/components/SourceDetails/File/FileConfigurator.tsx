import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {AttributeConfiguratorProps} from '../../../models';
import {isFileSource} from './model';
import {FileSelector} from './FileSelector';
import {InvalidAttributeSourceError} from '../error';

const FileConfigurator = ({source, validationErrors, onSourceChange}: AttributeConfiguratorProps) => {
  if (!isFileSource(source)) {
    throw new InvalidAttributeSourceError(`Invalid source data "${source.code}" for file configurator`);
  }

  return (
    <FileSelector
      selection={source.selection}
      validationErrors={filterErrors(validationErrors, '[selection]')}
      onSelectionChange={updatedFileSelection => onSourceChange({...source, selection: updatedFileSelection})}
    />
  );
};

export {FileConfigurator};
