import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {PropertyConfiguratorProps} from '../../../models';
import {CodeLabelSelector} from '../common/CodeLabelSelector';
import {isFamilyVariantSource} from './model';
import {InvalidPropertySourceError} from '../error';

const FamilyVariantConfigurator = ({source, validationErrors, onSourceChange}: PropertyConfiguratorProps) => {
  if (!isFamilyVariantSource(source)) {
    throw new InvalidPropertySourceError(`Invalid source data "${source.code}" for family variant configurator`);
  }

  return (
    <CodeLabelSelector
      selection={source.selection}
      validationErrors={filterErrors(validationErrors, '[selection]')}
      onSelectionChange={updatedSelection => onSourceChange({...source, selection: updatedSelection})}
    />
  );
};

export {FamilyVariantConfigurator};
