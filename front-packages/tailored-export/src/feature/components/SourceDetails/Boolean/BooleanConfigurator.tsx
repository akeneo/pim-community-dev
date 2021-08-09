import React from 'react';
import {AttributeConfiguratorProps} from '../../../models';
import {isBooleanSource} from './model';
import {InvalidAttributeSourceError} from '../error';
import {NoOperationsPlaceholder} from "../NoOperationsPlaceholder";

const BooleanConfigurator = ({source}: AttributeConfiguratorProps) => {
//  const translate = useTranslate();
//  const [isReplacementCollapsed, toggleReplacementCollapse] = useState<boolean>('replacement' in source.operations);

  if (!isBooleanSource(source)) {
    throw new InvalidAttributeSourceError(`Invalid source data "${source.code}" for boolean configurator`);
  }

  return <NoOperationsPlaceholder />;
/**
  return (
    <Collapse
      collapseButtonLabel={isReplacementCollapsed ? translate('pim_common.close') : translate('pim_common.open')}
      label={translate('akeneo.tailored_export.column_details.sources.operation.replacement.title')}
      isOpen={isReplacementCollapsed}
      onCollapse={toggleReplacementCollapse}
    >
      <BooleanReplacement
        trueLabel={translate('akeneo.tailored_export.column_details.sources.operation.replacement.yes')}
        falseLabel={translate('akeneo.tailored_export.column_details.sources.operation.replacement.no')}
        operation={source.operations.replacement}
        validationErrors={filterErrors(validationErrors, '[operations][replacement]')}
        onOperationChange={updatedOperation =>
          onSourceChange({...source, operations: {...source.operations, replacement: updatedOperation}})
        }
      />
    </Collapse>
  );
*/
};

export {BooleanConfigurator};
