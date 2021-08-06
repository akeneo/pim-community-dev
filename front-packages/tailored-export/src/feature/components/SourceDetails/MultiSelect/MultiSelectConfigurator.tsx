import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {AttributeConfiguratorProps} from '../../../models';
import {CodeLabelCollectionSelector} from '../common/CodeLabelCollectionSelector';
import {isMultiSelectSource} from './model';
import {InvalidAttributeSourceError} from '../error';

const MultiSelectConfigurator = ({source, validationErrors, onSourceChange}: AttributeConfiguratorProps) => {
  if (!isMultiSelectSource(source)) {
    throw new InvalidAttributeSourceError(`Invalid source data "${source.code}" for multi select configurator`);
  }

  return (
    <CodeLabelCollectionSelector
      selection={source.selection}
      validationErrors={filterErrors(validationErrors, '[selection]')}
      onSelectionChange={updatedSelection => onSourceChange({...source, selection: updatedSelection})}
    />
  );
};

export {MultiSelectConfigurator};
