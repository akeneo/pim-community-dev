import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {isNumberTarget} from './model';
import {NumberSelector} from './NumberSelector';
import {AttributeConfiguratorProps} from '../../../models/Configurator';
import {InvalidAttributeTargetError} from '../error/InvalidAttributeTargetError';

const NumberConfigurator = ({target, onTargetChange, validationErrors}: AttributeConfiguratorProps) => {
  if (!isNumberTarget(target)) {
    throw new InvalidAttributeTargetError(`Invalid target data "${target.code}" for number configurator`);
  }

  return (
    <div>
      <NumberSelector
        selection={target.selection}
        validationErrors={filterErrors(validationErrors, '[selection]')}
        onSelectionChange={updatedNumberSelection => onTargetChange({...target, selection: updatedNumberSelection})}
      />
    </div>
  );
};

export {NumberConfigurator};
