import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {PropertyConfiguratorProps} from '../../../models';
import {CodeLabelSelector} from '../common/CodeLabelSelector';
import {isFamilySource} from './model';
import {InvalidPropertySourceError} from '../error';

const FamilyConfigurator = ({source, validationErrors, onSourceChange}: PropertyConfiguratorProps) => {
  if (!isFamilySource(source)) {
    throw new InvalidPropertySourceError(`Invalid source data "${source.code}" for family configurator`);
  }

  return (
    <CodeLabelSelector
      selection={source.selection}
      validationErrors={filterErrors(validationErrors, '[selection]')}
      onSelectionChange={updatedSelection => onSourceChange({...source, selection: updatedSelection})}
    />
  );
};

export {FamilyConfigurator};
