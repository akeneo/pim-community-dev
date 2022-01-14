import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {AttributeConfiguratorProps} from '../../../models';
import {CodeLabelCollectionSelector, DefaultValue, Operations} from '../common';
import {isMultiSelectSource} from './model';
import {InvalidAttributeSourceError} from '../error';
import {AttributeOptionsReplacement} from '../common/AttributeOptionsReplacement';

const MultiSelectConfigurator = ({
  attribute,
  source,
  requirement,
  validationErrors,
  onSourceChange,
}: AttributeConfiguratorProps) => {
  if (!isMultiSelectSource(source)) {
    throw new InvalidAttributeSourceError(`Invalid source data "${source.code}" for multi select configurator`);
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
      <AttributeOptionsReplacement
        operation={source.operations.replacement}
        attributeCode={attribute.code}
        validationErrors={filterErrors(validationErrors, '[operations][replacement]')}
        onOperationChange={updatedOperation =>
          onSourceChange({...source, operations: {...source.operations, replacement: updatedOperation}})
        }
      />
      <CodeLabelCollectionSelector
        selection={source.selection}
        widthSeparator={'string_collection' !== requirement.type}
        validationErrors={filterErrors(validationErrors, '[selection]')}
        onSelectionChange={updatedSelection => onSourceChange({...source, selection: updatedSelection})}
      />
    </Operations>
  );
};

export {MultiSelectConfigurator};
