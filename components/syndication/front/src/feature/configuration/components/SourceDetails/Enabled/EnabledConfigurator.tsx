import React from 'react';
import {filterErrors, useTranslate} from '@akeneo-pim-community/shared';
import {BooleanReplacement, Operations} from '../common';
import {PropertyConfiguratorProps} from '../../../models';
import {isEnabledSource} from './model';
import {InvalidPropertySourceError} from '../error';
import {NoOperationsPlaceholder} from '../NoOperationsPlaceholder';

const EnabledConfigurator = ({source, requirement, validationErrors, onSourceChange}: PropertyConfiguratorProps) => {
  const translate = useTranslate();

  if (!isEnabledSource(source)) {
    throw new InvalidPropertySourceError(`Invalid source data "${source.code}" for enabled configurator`);
  }

  if ('string' !== requirement.type) return <NoOperationsPlaceholder />;

  return (
    <Operations>
      <BooleanReplacement
        trueLabel={translate('akeneo.syndication.data_mapping_details.sources.operation.replacement.enabled')}
        falseLabel={translate('akeneo.syndication.data_mapping_details.sources.operation.replacement.disabled')}
        operation={source.operations.replacement}
        validationErrors={filterErrors(validationErrors, '[operations][replacement]')}
        onOperationChange={updatedOperation =>
          onSourceChange({...source, operations: {...source.operations, replacement: updatedOperation}})
        }
      />
    </Operations>
  );
};

export {EnabledConfigurator};
