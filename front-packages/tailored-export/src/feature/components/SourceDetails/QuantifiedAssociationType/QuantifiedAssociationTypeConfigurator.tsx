import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {AssociationTypeConfiguratorProps} from '../../../models';
import {isQuantifiedAssociationTypeSource} from './model';
import {QuantifiedAssociationTypeSelector} from './QuantifiedAssociationTypeSelector';
import {InvalidAssociationTypeSourceError} from '../error';
import {Operations} from '../common';

const QuantifiedAssociationTypeConfigurator = ({
  source,
  validationErrors,
  onSourceChange,
}: AssociationTypeConfiguratorProps) => {
  if (!isQuantifiedAssociationTypeSource(source)) {
    throw new InvalidAssociationTypeSourceError(
      `Invalid source data "${source.code}" for quantified association configurator`
    );
  }

  return (
    <Operations>
      <QuantifiedAssociationTypeSelector
        selection={source.selection}
        validationErrors={filterErrors(validationErrors, '[selection]')}
        onSelectionChange={updatedSelection => onSourceChange({...source, selection: updatedSelection})}
      />
    </Operations>
  );
};

export {QuantifiedAssociationTypeConfigurator};
