import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {AttributeConfiguratorProps} from '../../../models';
import {CodeLabelSelector} from '../common/CodeLabelSelector';
import {isReferenceEntitySource} from './model';
import {InvalidAttributeSourceError} from '../error';

const ReferenceEntityConfigurator = ({source, validationErrors, onSourceChange}: AttributeConfiguratorProps) => {
  if (!isReferenceEntitySource(source)) {
    throw new InvalidAttributeSourceError(`Invalid source data "${source.code}" for reference entity configurator`);
  }

  return (
    <CodeLabelSelector
      selection={source.selection}
      validationErrors={filterErrors(validationErrors, '[selection]')}
      onSelectionChange={updatedSelection => onSourceChange({...source, selection: updatedSelection})}
    />
  );
};

export {ReferenceEntityConfigurator};
