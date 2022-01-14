import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {PropertyConfiguratorProps} from '../../../models';
import {CodeLabelCollectionSelector, Operations} from '../common';
import {isCategoriesSource} from './model';
import {InvalidPropertySourceError} from '../error';

const CategoriesConfigurator = ({source, validationErrors, onSourceChange}: PropertyConfiguratorProps) => {
  if (!isCategoriesSource(source)) {
    throw new InvalidPropertySourceError(`Invalid source data "${source.code}" for categories configurator`);
  }

  return (
    <Operations>
      <CodeLabelCollectionSelector
        selection={source.selection}
        validationErrors={filterErrors(validationErrors, '[selection]')}
        onSelectionChange={updatedSelection => onSourceChange({...source, selection: updatedSelection})}
      />
    </Operations>
  );
};

export {CategoriesConfigurator};
