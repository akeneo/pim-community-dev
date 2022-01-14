import {filterErrors} from '@akeneo-pim-community/shared';
import React from 'react';
import {AttributeConfiguratorProps} from '../../../models';
import {Operations, Extract} from '../common';
import {InvalidAttributeSourceError} from '../error';
import {isIdentifierSource} from './model';

const IdentifierConfigurator = ({source, validationErrors, onSourceChange}: AttributeConfiguratorProps) => {
  if (!isIdentifierSource(source)) {
    throw new InvalidAttributeSourceError(`Invalid source data "${source.code}" for identifier configurator`);
  }

  return (
    <Operations>
      <Extract
        operation={source.operations.extract}
        validationErrors={filterErrors(validationErrors, '[operations][extract]')}
        onOperationChange={updatedOperation =>
          onSourceChange({...source, operations: {...source.operations, extract: updatedOperation}})
        }
      />
    </Operations>
  );
};

export {IdentifierConfigurator};
