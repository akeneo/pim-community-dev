import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {AttributeConfiguratorProps} from '../../../models';
import {DefaultValue, Operations} from '../common';
import {CleanHTMLTags} from './CleanHTMLTags';
import {InvalidAttributeSourceError} from '../error';
import {isTextSource} from './model';

const TextConfigurator = ({source, validationErrors, onSourceChange}: AttributeConfiguratorProps) => {
  if (!isTextSource(source)) {
    throw new InvalidAttributeSourceError(`Invalid source data "${source.code}" for text configurator`);
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
      <CleanHTMLTags
        operation={source.operations.clean_html_tags}
        onOperationChange={updatedOperation =>
          onSourceChange({...source, operations: {...source.operations, clean_html_tags: updatedOperation}})
        }
      />
    </Operations>
  );
};

export {TextConfigurator};
