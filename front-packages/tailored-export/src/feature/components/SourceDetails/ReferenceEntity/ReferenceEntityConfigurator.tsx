import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {AttributeConfiguratorProps} from '../../../models';
import {CodeLabelSelector, DefaultValue, Operations} from '../common';
import {isReferenceEntitySource} from './model';
import {InvalidAttributeSourceError} from '../error';
import {ReferenceEntityReplacement} from "./ReferenceEntityReplacement";

const ReferenceEntityConfigurator = ({attribute, source, validationErrors, onSourceChange}: AttributeConfiguratorProps) => {
  if (!isReferenceEntitySource(source)) {
    throw new InvalidAttributeSourceError(`Invalid source data "${source.code}" for reference entity configurator`);
  }

  if (undefined === attribute.reference_data_name) {
    throw new Error(`Reference entity attribute "${attribute.code}" should have a reference_data_name`);
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
      <ReferenceEntityReplacement
        operation={source.operations.replacement}
        referenceEntityCode={attribute.reference_data_name}
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
    </Operations>
  );
};

export {ReferenceEntityConfigurator};
