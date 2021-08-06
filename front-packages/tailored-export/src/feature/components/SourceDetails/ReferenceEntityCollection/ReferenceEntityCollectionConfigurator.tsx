import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {AttributeConfiguratorProps} from '../../../models';
import {CodeLabelCollectionSelector} from '../common/CodeLabelCollectionSelector';
import {isReferenceEntityCollectionSource} from './model';
import {InvalidAttributeSourceError} from '../error';

const ReferenceEntityCollectionConfigurator = ({
  source,
  validationErrors,
  onSourceChange,
}: AttributeConfiguratorProps) => {
  if (!isReferenceEntityCollectionSource(source)) {
    throw new InvalidAttributeSourceError(
      `Invalid source data "${source.code}" for reference entity collection configurator`
    );
  }

  return (
    <CodeLabelCollectionSelector
      selection={source.selection}
      validationErrors={filterErrors(validationErrors, '[selection]')}
      onSelectionChange={updatedSelection => onSourceChange({...source, selection: updatedSelection})}
    />
  );
};

export {ReferenceEntityCollectionConfigurator};
