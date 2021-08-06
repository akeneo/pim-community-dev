import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {AttributeConfiguratorProps} from '../../../models';
import {CodeLabelCollectionSelector} from '../common/CodeLabelCollectionSelector';
import {isAssetCollectionSource} from './model';
import {InvalidAttributeSourceError} from '../error';

const AssetCollectionConfigurator = ({source, validationErrors, onSourceChange}: AttributeConfiguratorProps) => {
  if (!isAssetCollectionSource(source)) {
    throw new InvalidAttributeSourceError(`Invalid source data "${source.code}" for asset collection configurator`);
  }

  return (
    <CodeLabelCollectionSelector
      selection={source.selection}
      validationErrors={filterErrors(validationErrors, '[selection]')}
      onSelectionChange={updatedSelection => onSourceChange({...source, selection: updatedSelection})}
    />
  );
};

export {AssetCollectionConfigurator};
