import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {AttributeConfiguratorProps} from '../../../models';
import {CodeLabelCollectionSelector, DefaultValue, Operations} from '../common';
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
    <Operations>
      <DefaultValue
        operation={source.operations.default_value}
        validationErrors={filterErrors(validationErrors, '[operations][default_value]')}
        onOperationChange={updatedOperation =>
          onSourceChange({...source, operations: {...source.operations, default_value: updatedOperation}})
        }
      />
      <CodeLabelCollectionSelector
        selection={source.selection}
        validationErrors={filterErrors(validationErrors, '[selection]')}
        onSelectionChange={updatedSelection => onSourceChange({...source, selection: updatedSelection})}
      />
    </Operations>
  );
};

export {ReferenceEntityCollectionConfigurator};
