import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {PropertyConfiguratorProps} from '../../../models';
import {ParentSelector} from './ParentSelector';
import {isParentSource} from './model';
import {InvalidPropertySourceError} from '../error';
import {Operations} from '../common';

const ParentConfigurator = ({source, validationErrors, onSourceChange}: PropertyConfiguratorProps) => {
  if (!isParentSource(source)) {
    throw new InvalidPropertySourceError(`Invalid source data "${source.code}" for parent configurator`);
  }

  return (
    <Operations>
      <ParentSelector
        selection={source.selection}
        validationErrors={filterErrors(validationErrors, '[selection]')}
        onSelectionChange={updatedSelection => onSourceChange({...source, selection: updatedSelection})}
      />
    </Operations>
  );
};

export {ParentConfigurator};
