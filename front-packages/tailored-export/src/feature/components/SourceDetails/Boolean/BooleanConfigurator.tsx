import React from 'react';
import {filterErrors, useTranslate} from '@akeneo-pim-community/shared';
import {AttributeConfiguratorProps} from '../../../models';
import {isBooleanSource} from './model';
import {InvalidAttributeSourceError} from '../error';
import {BooleanReplacement, DefaultValue, Operations} from '../common';

const BooleanConfigurator = ({source, validationErrors, onSourceChange}: AttributeConfiguratorProps) => {
  const translate = useTranslate();

  if (!isBooleanSource(source)) {
    throw new InvalidAttributeSourceError(`Invalid source data "${source.code}" for boolean configurator`);
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
      <BooleanReplacement
        trueLabel={translate('akeneo.tailored_export.column_details.sources.operation.replacement.yes')}
        falseLabel={translate('akeneo.tailored_export.column_details.sources.operation.replacement.no')}
        operation={source.operations.replacement}
        validationErrors={filterErrors(validationErrors, '[operations][replacement]')}
        onOperationChange={updatedOperation =>
          onSourceChange({...source, operations: {...source.operations, replacement: updatedOperation}})
        }
      />
    </Operations>
  );
};

export {BooleanConfigurator};
