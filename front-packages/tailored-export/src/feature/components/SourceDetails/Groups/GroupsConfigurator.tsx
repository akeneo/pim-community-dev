import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {PropertyConfiguratorProps} from '../../../models';
import {CodeLabelCollectionSelector} from '../common/CodeLabelCollectionSelector';
import {isGroupsSource} from './model';
import {InvalidPropertySourceError} from '../error';

const GroupsConfigurator = ({source, validationErrors, onSourceChange}: PropertyConfiguratorProps) => {
  if (!isGroupsSource(source)) {
    throw new InvalidPropertySourceError(`Invalid source data "${source.code}" for groups configurator`);
  }

  return (
    <CodeLabelCollectionSelector
      selection={source.selection}
      validationErrors={filterErrors(validationErrors, '[selection]')}
      onSelectionChange={updatedSelection => onSourceChange({...source, selection: updatedSelection})}
    />
  );
};

export {GroupsConfigurator};
