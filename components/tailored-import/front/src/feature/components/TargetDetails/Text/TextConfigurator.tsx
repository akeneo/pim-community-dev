import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {isTextTarget} from './model';
import {TextSelector} from './TextSelector';
import {AttributeConfiguratorProps} from '../../../models/Configurator';
import {InvalidAttributeTargetError} from "../error/InvalidAttributeTargetError";

const TextConfigurator = ({target, onTargetChange, validationErrors}: AttributeConfiguratorProps) => {
  if (!isTextTarget(target)) {
    throw new InvalidAttributeTargetError(`Invalid target data "${target.code}" for number configurator`);
  }

  return (
    <div>
      <TextSelector
        selection={target.selection}
        validationErrors={filterErrors(validationErrors, '[selection]')}
        onSelectionChange={updatedTextSelection => onTargetChange({...target, selection: updatedTextSelection})}
      />
    </div>
  );
};

export {TextConfigurator};
