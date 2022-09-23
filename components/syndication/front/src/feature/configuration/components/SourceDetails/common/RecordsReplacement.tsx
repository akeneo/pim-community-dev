import React, {useState} from 'react';
import styled from 'styled-components';
import {Button, Collapse, Pill, useBooleanState} from 'akeneo-design-system';
import {useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {
  getDefaultReplacementOperation,
  isDefaultReplacementOperation,
  ReplacementModal,
  ReplacementOperation,
  ReplacementValues,
  ReplacementValueFilter,
  getDefaultReplacementValueFilter,
} from '../common';
import {RECORD_PAGE_SIZE, useRecords} from '../../../hooks';

const EditMappingButton = styled(Button)`
  margin: 2px 2px 10px;
`;

type RecordsReplacementProps = {
  referenceEntityCode: string;
  operation?: ReplacementOperation;
  validationErrors: ValidationError[];
  onOperationChange: (updatedOperation?: ReplacementOperation) => void;
};

const RecordsReplacement = ({
  operation = getDefaultReplacementOperation(),
  referenceEntityCode,
  validationErrors,
  onOperationChange,
}: RecordsReplacementProps) => {
  const translate = useTranslate();
  const [isReplacementCollapsed, toggleReplacementCollapse] = useState<boolean>(false);
  const [isModalOpen, openModal, closeModal] = useBooleanState();
  const [replacementValueFilter, setReplacementValueFilter] = useState<ReplacementValueFilter>(
    getDefaultReplacementValueFilter()
  );

  const [records, totalItems] = useRecords(
    referenceEntityCode,
    replacementValueFilter.searchValue,
    replacementValueFilter.page,
    replacementValueFilter.codesToInclude,
    replacementValueFilter.codesToExclude,
    isModalOpen
  );

  const handleCancel = () => {
    closeModal();
    setReplacementValueFilter(getDefaultReplacementValueFilter());
  };

  const handleConfirm = (mapping: ReplacementValues) => {
    const newOperation = {...operation, mapping};

    onOperationChange(isDefaultReplacementOperation(newOperation) ? undefined : newOperation);
    closeModal();
    setReplacementValueFilter(getDefaultReplacementValueFilter());
  };

  return (
    <Collapse
      collapseButtonLabel={isReplacementCollapsed ? translate('pim_common.close') : translate('pim_common.open')}
      label={
        <>
          {translate('akeneo.syndication.data_mapping_details.sources.operation.replacement.title')}
          {0 === validationErrors.length && !isDefaultReplacementOperation(operation) && <Pill level="primary" />}
          {0 < validationErrors.length && <Pill level="danger" />}
        </>
      }
      isOpen={isReplacementCollapsed}
      onCollapse={toggleReplacementCollapse}
    >
      <EditMappingButton ghost={true} level="secondary" onClick={openModal}>
        {translate('akeneo.syndication.data_mapping_details.sources.operation.replacement.edit_mapping')}
      </EditMappingButton>
      {isModalOpen && (
        <ReplacementModal
          title={translate('akeneo.syndication.data_mapping_details.sources.operation.replacement.modal.records')}
          replacementValueFilter={replacementValueFilter}
          onReplacementValueFilterChange={setReplacementValueFilter}
          values={records}
          totalItems={totalItems}
          itemsPerPage={RECORD_PAGE_SIZE}
          initialMapping={operation.mapping}
          validationErrors={validationErrors}
          onConfirm={handleConfirm}
          onCancel={handleCancel}
        />
      )}
    </Collapse>
  );
};

export {RecordsReplacement};
