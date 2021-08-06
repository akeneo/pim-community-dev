import React, {useState} from 'react';
import {Collapse, getColor, getFontSize} from 'akeneo-design-system';
import styled from 'styled-components';
import {filterErrors, useTranslate} from '@akeneo-pim-community/shared';
import {AttributeConfiguratorProps} from '../../../models';
import {isBooleanSource} from './model';
import {BooleanReplacement} from '../common/BooleanReplacement';
import {InvalidAttributeSourceError} from '../error';
import {DefaultValue} from '../common/DefaultValue';

const OperationHeader = styled.div`
  font-size: ${getFontSize('bigger')};
  color: ${getColor('grey', 140)};
  margin: 20px 0;
`;

const BooleanConfigurator = ({source, validationErrors, onSourceChange}: AttributeConfiguratorProps) => {
  const translate = useTranslate();
  const [isReplacementCollapsed, toggleReplacementCollapse] = useState<boolean>('replacement' in source.operations);
  const [isDefaultValueCollapsed, toggleDefaultValueCollapse] = useState<boolean>('default_value' in source.operations);

  if (!isBooleanSource(source)) {
    throw new InvalidAttributeSourceError(`Invalid source data "${source.code}" for boolean configurator`);
  }

  return (
    <div>
      <OperationHeader>{translate('akeneo.tailored_export.column_details.sources.operation.header')}</OperationHeader>
      <div>
        <Collapse
          collapseButtonLabel={isDefaultValueCollapsed ? translate('pim_common.close') : translate('pim_common.open')}
          label={translate('akeneo.tailored_export.column_details.sources.operation.default_value.title')}
          isOpen={isDefaultValueCollapsed}
          onCollapse={toggleDefaultValueCollapse}
        >
          <DefaultValue
            operation={source.operations.default_value}
            validationErrors={filterErrors(validationErrors, '[operations][default_value]')}
            onOperationChange={updatedOperation =>
              onSourceChange({...source, operations: {...source.operations, default_value: updatedOperation}})
            }
          />
        </Collapse>
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
      </div>
    </div>
  );
};

export {BooleanConfigurator};
