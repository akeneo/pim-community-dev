import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {AttributeConfiguratorProps} from '../../../models';
import {DateSelector} from './DateSelector';
import {isDateSource} from './model';
import {InvalidAttributeSourceError} from '../error';

const DateConfigurator = ({source, validationErrors, onSourceChange}: AttributeConfiguratorProps) => {
  if (!isDateSource(source)) {
    throw new InvalidAttributeSourceError(`Invalid source data "${source.code}" for date configurator`);
  }

  return (
    <DateSelector
      selection={source.selection}
      validationErrors={filterErrors(validationErrors, '[selection]')}
      onSelectionChange={updatedSelection => onSourceChange({...source, selection: updatedSelection})}
    />
  );
};

export {DateConfigurator};
