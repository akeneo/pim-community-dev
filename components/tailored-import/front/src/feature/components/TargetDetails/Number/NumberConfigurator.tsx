import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {isNumberTarget} from './model';
import {NumberSelector} from './NumberSelector';
import {AttributeConfiguratorProps} from "../../../models/Configurator";

const NumberConfigurator = ({target, onTargetChange, validationErrors}: AttributeConfiguratorProps) => {
  if (!isNumberTarget(target)) {
    throw new Error('toto');
    // throw new InvalidAttributeSourceError(`Invalid target data "${target.code}" for number configurator`);
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
