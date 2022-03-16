import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {isNumberTarget} from './model';
import {NumberDecimalSeparator} from './NumberDecimalSeparator';
import {AttributeConfiguratorProps} from '../../../models/Configurator';
import {InvalidAttributeTargetError} from '../error/InvalidAttributeTargetError';

const NumberConfigurator = ({target, attribute, onTargetChange, validationErrors}: AttributeConfiguratorProps) => {
  if (!isNumberTarget(target)) {
    throw new InvalidAttributeTargetError(`Invalid target data "${target.code}" for number configurator`);
  }

  return (
    <>
      {attribute.decimals_allowed && (
        <NumberDecimalSeparator
          parameters={target.parameters}
          validationErrors={filterErrors(validationErrors, '[selection]')}
          onParametersChange={updatedNumberParameters => onTargetChange({...target, parameters: updatedNumberParameters})}
        />
      )}
    </>
  );
};

export {NumberConfigurator};
