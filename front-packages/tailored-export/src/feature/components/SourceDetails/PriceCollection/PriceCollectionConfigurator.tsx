import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {AttributeConfiguratorProps} from '../../../models';
import {PriceCollectionSelector} from './PriceCollectionSelector';
import {isPriceCollectionSource} from './model';
import {InvalidAttributeSourceError} from '../error';
import {DefaultValue, Operations} from '../common';

const PriceCollectionConfigurator = ({source, validationErrors, onSourceChange}: AttributeConfiguratorProps) => {
  if (!isPriceCollectionSource(source)) {
    throw new InvalidAttributeSourceError(`Invalid source data "${source.code}" for price collection configurator`);
  }

  return (
    <Operations>
      <DefaultValue
        operation={source.operations.default_value}
        validationErrors={filterErrors(validationErrors, '[operations][default_value]')}
        onOperationChange={updatedOperation =>
          onSourceChange({...source, operations: {...source.operations, default_value: updatedOperation}})
        }
      />
      <PriceCollectionSelector
        selection={source.selection}
        validationErrors={filterErrors(validationErrors, '[selection]')}
        onSelectionChange={updatedPriceCollectionSelection =>
          onSourceChange({...source, selection: updatedPriceCollectionSelection})
        }
      />
    </Operations>
  );
};

export {PriceCollectionConfigurator};
