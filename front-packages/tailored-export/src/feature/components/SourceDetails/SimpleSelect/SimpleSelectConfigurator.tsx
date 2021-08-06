import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {AttributeConfiguratorProps} from '../../../models';
import {CodeLabelSelector} from '../common/CodeLabelSelector';
import {isSimpleSelectSource} from './model';
import {InvalidAttributeSourceError} from '../error';

const SimpleSelectConfigurator = ({source, validationErrors, onSourceChange}: AttributeConfiguratorProps) => {
  if (!isSimpleSelectSource(source)) {
    throw new InvalidAttributeSourceError(`Invalid source data "${source.code}" for simple select configurator`);
  }

  return (
    <CodeLabelSelector
      selection={source.selection}
      validationErrors={filterErrors(validationErrors, '[selection]')}
      onSelectionChange={updatedSelection => onSourceChange({...source, selection: updatedSelection})}
    />
  );
};

export {SimpleSelectConfigurator};
