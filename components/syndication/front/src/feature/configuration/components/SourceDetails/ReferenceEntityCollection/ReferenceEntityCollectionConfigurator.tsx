import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {AttributeConfiguratorProps} from '../../../models';
import {CodeLabelCollectionSelector, DefaultValue, Operations, RecordsReplacement} from '../common';
import {isReferenceEntityCollectionSource} from './model';
import {InvalidAttributeSourceError} from '../error';
import {NoOperationsPlaceholder} from '../../SourceDetails/NoOperationsPlaceholder';

const ReferenceEntityCollectionConfigurator = ({
  source,
  attribute,
  requirement,
  validationErrors,
  onSourceChange,
}: AttributeConfiguratorProps) => {
  if (!isReferenceEntityCollectionSource(source)) {
    throw new InvalidAttributeSourceError(
      `Invalid source data "${source.code}" for reference entity collection configurator`
    );
  }

  if (undefined === attribute.reference_data_name) {
    throw new Error(`Reference entity collection attribute "${attribute.code}" should have a reference_data_name`);
  }

  if ('string_collection' === requirement.type) {
    return <NoOperationsPlaceholder />;
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
      <RecordsReplacement
        operation={source.operations.replacement}
        referenceEntityCode={attribute.reference_data_name}
        validationErrors={filterErrors(validationErrors, '[operations][replacement]')}
        onOperationChange={updatedOperation =>
          onSourceChange({...source, operations: {...source.operations, replacement: updatedOperation}})
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
