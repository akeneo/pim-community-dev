import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {AttributeConfiguratorProps} from '../../../models';
import {DefaultValue, Extract, Operations, Split} from '../common';
import {CleanHTMLTags} from './CleanHTMLTags';
import {InvalidAttributeSourceError} from '../error';
import {isTextSource} from './model';

const TextConfigurator = ({source, requirement, validationErrors, onSourceChange}: AttributeConfiguratorProps) => {
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
      <Extract
        operation={source.operations.extract}
        validationErrors={filterErrors(validationErrors, '[operations][extract]')}
        onOperationChange={updatedOperation =>
          onSourceChange({...source, operations: {...source.operations, extract: updatedOperation}})
        }
      />
      {'string_collection' === requirement.type && (
        <Split
          operation={source.operations.split}
          validationErrors={filterErrors(validationErrors, '[operations][split]')}
          onOperationChange={updatedOperation =>
            onSourceChange({...source, operations: {...source.operations, split: updatedOperation}})
          }
        />
      )}
    </Operations>
  );
};

export {TextConfigurator};
