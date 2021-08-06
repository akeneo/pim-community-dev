import React from 'react';
import {filterErrors, useTranslate} from '@akeneo-pim-community/shared';
import {BooleanReplacement} from '../common/BooleanReplacement';
import {PropertyConfiguratorProps} from '../../../models';
import {isEnabledSource} from './model';
import {InvalidPropertySourceError} from '../error';

const EnabledConfigurator = ({source, validationErrors, onSourceChange}: PropertyConfiguratorProps) => {
  const translate = useTranslate();

  if (!isEnabledSource(source)) {
    throw new InvalidPropertySourceError(`Invalid source data "${source.code}" for enabled configurator`);
  }

  return (
    <BooleanReplacement
      trueLabel={translate('akeneo.tailored_export.column_details.sources.operation.replacement.enabled')}
      falseLabel={translate('akeneo.tailored_export.column_details.sources.operation.replacement.disabled')}
      operation={source.operations.replacement}
      validationErrors={filterErrors(validationErrors, '[operations][replacement]')}
      onOperationChange={updatedOperation =>
        onSourceChange({...source, operations: {...source.operations, replacement: updatedOperation}})
      }
    />
  );
};

export {EnabledConfigurator};
