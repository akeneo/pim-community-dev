import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {isNumberTarget} from './model';
import {NumberSelector} from './NumberSelector';
import {AttributeConfiguratorProps} from '../../../models/Configurator';
import {InvalidAttributeTargetError} from '../error/InvalidAttributeTargetError';

const NumberConfigurator = ({target, attribute, onTargetChange, validationErrors}: AttributeConfiguratorProps) => {
  if (!isNumberTarget(target)) {
    throw new InvalidAttributeTargetError(`Invalid target data "${target.code}" for number configurator`);
  }

  return (
    <>
      {attribute.decimals_allowed && (
        <NumberSelector
          configuration={target.configuration}
          validationErrors={filterErrors(validationErrors, '[selection]')}
          onConfigurationChange={updatedNumberConfiguration => onTargetChange({...target, configuration: updatedNumberConfiguration})}
        />
      )}
    </>
  );
};

export {NumberConfigurator};
