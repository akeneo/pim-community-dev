import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {isNumberSource} from './model';
import {AttributeConfiguratorProps} from '../../../models';
import {NumberSelector} from './NumberSelector';
import {InvalidAttributeSourceError} from '../error';

const NumberConfigurator = ({source, onSourceChange, validationErrors}: AttributeConfiguratorProps) => {
  if (!isNumberSource(source)) {
    throw new InvalidAttributeSourceError(`Invalid source data "${source.code}" for number configurator`);
  }

  return (
    <NumberSelector
      selection={source.selection}
      validationErrors={filterErrors(validationErrors, '[selection]')}
      onSelectionChange={updatedNumberSelection => onSourceChange({...source, selection: updatedNumberSelection})}
    />
  );
};

export {NumberConfigurator};
