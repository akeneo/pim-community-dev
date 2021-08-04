import React, {useState} from 'react';
import {Collapse} from 'akeneo-design-system';
import {filterErrors, useTranslate} from '@akeneo-pim-community/shared';
import {BooleanReplacement} from '../common/BooleanReplacement';
import {PropertyConfiguratorProps} from '../../../models';
import {isEnabledSource} from './model';
import {InvalidPropertySourceError} from '../error';

const EnabledConfigurator = ({source, validationErrors, onSourceChange}: PropertyConfiguratorProps) => {
  const translate = useTranslate();
  const [isReplacementCollapsed, toggleReplacementCollapse] = useState<boolean>(true);

  if (!isEnabledSource(source)) {
    throw new InvalidPropertySourceError(`Invalid source data "${source.code}" for enabled configurator`);
  }

  return (
    <Collapse
      collapseButtonLabel={isReplacementCollapsed ? translate('pim_common.close') : translate('pim_common.open')}
      label={translate('akeneo.tailored_export.column_details.sources.operation.replacement.title')}
      isOpen={isReplacementCollapsed}
      onCollapse={toggleReplacementCollapse}
    >
      <BooleanReplacement
        operation={source.operations.replacement}
        validationErrors={filterErrors(validationErrors, '[operations][replacement]')}
        onOperationChange={updatedOperation =>
          onSourceChange({...source, operations: {...source.operations, replacement: updatedOperation}})
        }
      />
    </Collapse>
  );
};

export {EnabledConfigurator};
