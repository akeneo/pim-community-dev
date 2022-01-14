import React, {useState} from 'react';
import {filterErrors, TextField, useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {Collapse, Pill} from 'akeneo-design-system';

type ExtractOperation = {
  type: 'extract';
  regexp: string;
};

const isExtractOperation = (operation?: any): operation is ExtractOperation =>
  undefined !== operation && 'type' in operation && 'extract' === operation.type && 'regexp' in operation;

const getDefaultExtractOperation = (): ExtractOperation => ({
  type: 'extract',
  regexp: '',
});

const isDefaultExtractOperation = (operation?: ExtractOperation) =>
  'extract' === operation?.type && '' === operation.regexp;

type ExtractProps = {
  operation?: ExtractOperation;
  validationErrors: ValidationError[];
  onOperationChange: (updatedOperation?: ExtractOperation) => void;
};

const Extract = ({operation = getDefaultExtractOperation(), validationErrors, onOperationChange}: ExtractProps) => {
  const translate = useTranslate();
  const [isExtractCollapsed, toggleExtractCollapse] = useState<boolean>(false);

  return (
    <Collapse
      collapseButtonLabel={isExtractCollapsed ? translate('pim_common.close') : translate('pim_common.open')}
      label={
        <>
          {translate('akeneo.syndication.data_mapping_details.sources.operation.extract.title')}
          {0 === validationErrors.length && !isDefaultExtractOperation(operation) && <Pill level="primary" />}
          {0 < validationErrors.length && <Pill level="danger" />}
        </>
      }
      isOpen={isExtractCollapsed}
      onCollapse={toggleExtractCollapse}
    >
      <TextField
        value={operation.regexp}
        label={translate('akeneo.syndication.data_mapping_details.sources.operation.extract.label')}
        placeholder={translate('akeneo.syndication.data_mapping_details.sources.operation.extract.placeholder')}
        errors={filterErrors(validationErrors, '[regexp]')}
        onChange={newRegexp => {
          const newOperation = {...operation, regexp: newRegexp};

          onOperationChange(isDefaultExtractOperation(newOperation) ? undefined : newOperation);
        }}
      />
    </Collapse>
  );
};

export {Extract, getDefaultExtractOperation, isExtractOperation};
export type {ExtractOperation};
