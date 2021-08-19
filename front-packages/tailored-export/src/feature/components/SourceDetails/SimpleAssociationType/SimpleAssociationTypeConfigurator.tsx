import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {AssociationTypeConfiguratorProps} from '../../../models';
import {isSimpleAssociationTypeSource} from './model';
import {SimpleAssociationTypeSelector} from './SimpleAssociationTypeSelector';
import {InvalidAssociationTypeSourceError} from '../error';
import {Operations} from '../common';

const SimpleAssociationTypeConfigurator = ({
  source,
  validationErrors,
  onSourceChange,
}: AssociationTypeConfiguratorProps) => {
  if (!isSimpleAssociationTypeSource(source)) {
    throw new InvalidAssociationTypeSourceError(`Invalid source data "${source.code}" for association configurator`);
  }

  return (
    <Operations>
      <SimpleAssociationTypeSelector
        selection={source.selection}
        validationErrors={filterErrors(validationErrors, '[selection]')}
        onSelectionChange={updatedSelection => onSourceChange({...source, selection: updatedSelection})}
      />
    </Operations>
  );
};

export {SimpleAssociationTypeConfigurator};
