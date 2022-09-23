import React, {useState} from 'react';
import {filterErrors, TextField, useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {Collapse, Pill} from 'akeneo-design-system';

type SplitOperation = {
  type: 'split';
  separator: string;
};

const isSplitOperation = (operation?: any): operation is SplitOperation =>
  undefined !== operation && 'type' in operation && 'split' === operation.type && 'separator' in operation;

const getDefaultSplitOperation = (): SplitOperation => ({
  type: 'split',
  separator: '',
});

const isDefaultSplitOperation = (operation?: SplitOperation) =>
  'split' === operation?.type && '' === operation.separator;

type SplitProps = {
  operation?: SplitOperation;
  validationErrors: ValidationError[];
  onOperationChange: (updatedOperation?: SplitOperation) => void;
};

const Split = ({operation = getDefaultSplitOperation(), validationErrors, onOperationChange}: SplitProps) => {
  const translate = useTranslate();
  const [isSplitCollapsed, toggleSplitCollapse] = useState<boolean>(false);

  return (
    <Collapse
      collapseButtonLabel={isSplitCollapsed ? translate('pim_common.close') : translate('pim_common.open')}
      label={
        <>
          {translate('akeneo.syndication.data_mapping_details.sources.operation.split.title')}
          {0 === validationErrors.length && !isDefaultSplitOperation(operation) && <Pill level="primary" />}
          {0 < validationErrors.length && <Pill level="danger" />}
        </>
      }
      isOpen={isSplitCollapsed}
      onCollapse={toggleSplitCollapse}
    >
      <TextField
        value={operation.separator}
        label={translate('akeneo.syndication.data_mapping_details.sources.operation.split.label')}
        placeholder={translate('akeneo.syndication.data_mapping_details.sources.operation.split.placeholder')}
        errors={filterErrors(validationErrors, '[separator]')}
        onChange={newRegexp => {
          const newOperation = {...operation, separator: newRegexp};

          onOperationChange(isDefaultSplitOperation(newOperation) ? undefined : newOperation);
        }}
      />
    </Collapse>
  );
};

export {Split, getDefaultSplitOperation, isSplitOperation};
export type {SplitOperation};
