import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {AttributeConfiguratorProps} from '../../../models';
import {AssetCollectionMediaFileUrlSelection, isAssetCollectionSource} from './model';
import {InvalidAttributeSourceError} from '../error';
import {DefaultValue, Operations} from '../common';
import {AssetCollectionSelector} from './AssetCollectionSelector';
import {AssetCollectionUrlSelector} from './AssetCollectionUrlSelector';

const AssetCollectionConfigurator = ({
  source,
  requirement,
  attribute,
  validationErrors,
  onSourceChange,
}: AttributeConfiguratorProps) => {
  if (!isAssetCollectionSource(source)) {
    throw new InvalidAttributeSourceError(`Invalid source data "${source.code}" for asset collection configurator`);
  }

  if (undefined === attribute.reference_data_name) {
    throw new Error(`Asset collection attribute "${attribute.code}" should have a reference_data_name`);
  }

  if ('url' === requirement.type) {
    return (
      <Operations>
        <AssetCollectionUrlSelector
          assetFamilyCode={attribute.reference_data_name}
          selection={source.selection as AssetCollectionMediaFileUrlSelection}
          validationErrors={filterErrors(validationErrors, '[selection]')}
          onSelectionChange={updatedSelection => onSourceChange({...source, selection: updatedSelection})}
        />
      </Operations>
    );
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
      <AssetCollectionSelector
        assetFamilyCode={attribute.reference_data_name}
        selection={source.selection}
        validationErrors={filterErrors(validationErrors, '[selection]')}
        onSelectionChange={updatedSelection => onSourceChange({...source, selection: updatedSelection})}
      />
    </Operations>
  );
};

export {AssetCollectionConfigurator};
