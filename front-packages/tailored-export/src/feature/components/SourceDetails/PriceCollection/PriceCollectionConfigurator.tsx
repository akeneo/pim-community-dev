import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {AttributeConfiguratorProps} from '../../../models';
import {PriceCollectionSelector} from './PriceCollectionSelector';
import {isPriceCollectionSource} from './model';
import {InvalidAttributeSourceError} from '../error';

const PriceCollectionConfigurator = ({source, validationErrors, onSourceChange}: AttributeConfiguratorProps) => {
  if (!isPriceCollectionSource(source)) {
    throw new InvalidAttributeSourceError(`Invalid source data "${source.code}" for price collection configurator`);
  }

  return (
    <PriceCollectionSelector
      selection={source.selection}
      validationErrors={filterErrors(validationErrors, '[selection]')}
      onSelectionChange={updatedPriceCollectionSelection =>
        onSourceChange({...source, selection: updatedPriceCollectionSelection})
      }
    />
  );
};

export {PriceCollectionConfigurator};
