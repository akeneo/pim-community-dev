import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {AttributeConfiguratorProps} from '../../../models';
import {CodeLabelSelector, DefaultValue, Extract, Operations} from '../common';
import {isSimpleSelectSource} from './model';
import {InvalidAttributeSourceError} from '../error';
import {AttributeOptionsReplacement} from '../common/AttributeOptionsReplacement';

const SimpleSelectConfigurator = ({
  source,
  attribute,
  validationErrors,
  onSourceChange,
}: AttributeConfiguratorProps) => {
  if (!isSimpleSelectSource(source)) {
    throw new InvalidAttributeSourceError(`Invalid source data "${source.code}" for simple select configurator`);
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
      <CodeLabelSelector
        selection={source.selection}
        validationErrors={filterErrors(validationErrors, '[selection]')}
        onSelectionChange={updatedSelection => onSourceChange({...source, selection: updatedSelection})}
      />
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

export {SimpleSelectConfigurator};
