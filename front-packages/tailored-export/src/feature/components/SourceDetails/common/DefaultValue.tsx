import React, {useState} from 'react';
import {filterErrors, TextField, useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {Collapse} from 'akeneo-design-system';

type DefaultValueOperation = {
  type: 'default_value';
  value: string;
};

const isDefaultValueOperation = (operation?: any): operation is DefaultValueOperation =>
  undefined !== operation && 'type' in operation && 'default_value' === operation.type && 'value' in operation;

const getDefaultDefaultValueOperation = (): DefaultValueOperation => ({
  type: 'default_value',
  value: '',
});

const isDefaultDefaultValueOperation = (operation?: DefaultValueOperation) =>
  'default_value' === operation?.type && '' === operation.value;

type DefaultValueProps = {
  operation?: DefaultValueOperation;
  validationErrors: ValidationError[];
  onOperationChange: (updatedOperation?: DefaultValueOperation) => void;
};

const DefaultValue = ({
  operation = getDefaultDefaultValueOperation(),
  validationErrors,
  onOperationChange,
}: DefaultValueProps) => {
  const translate = useTranslate();
  const [isDefaultValueCollapsed, toggleDefaultValueCollapse] = useState<boolean>(
    !isDefaultDefaultValueOperation(operation)
  );

  return (
    <Collapse
      collapseButtonLabel={isDefaultValueCollapsed ? translate('pim_common.close') : translate('pim_common.open')}
      label={translate('akeneo.tailored_export.column_details.sources.operation.default_value.title')}
      isOpen={isDefaultValueCollapsed}
      onCollapse={toggleDefaultValueCollapse}
    >
      <TextField
        value={operation.value}
        label={translate('akeneo.tailored_export.column_details.sources.operation.default_value.label')}
        placeholder={translate('akeneo.tailored_export.column_details.sources.operation.default_value.placeholder')}
        errors={filterErrors(validationErrors, '[value]')}
        onChange={newValue => {
          const newOperation = {...operation, value: newValue};

          onOperationChange(isDefaultDefaultValueOperation(newOperation) ? undefined : newOperation);
        }}
      />
    </Collapse>
  );
};

export {DefaultValue, getDefaultDefaultValueOperation, isDefaultValueOperation};
export type {DefaultValueOperation};
