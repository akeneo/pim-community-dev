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
import {useAttributeOptions, OPTION_COLLECTION_PAGE_SIZE} from '../../../hooks/useAttributeOptions';

const EditMappingButton = styled(Button)`
  margin: 2px 2px 10px;
`;

type MultiSelectReplacementProps = {
  operation?: ReplacementOperation;
  attributeCode: string;
  validationErrors: ValidationError[];
  onOperationChange: (updatedOperation?: ReplacementOperation) => void;
};

const MultiSelectReplacement = ({
  operation = getDefaultReplacementOperation(),
  attributeCode,
  validationErrors,
  onOperationChange,
}: MultiSelectReplacementProps) => {
  const translate = useTranslate();
  const [isReplacementCollapsed, toggleReplacementCollapse] = useState<boolean>(false);
  const [isModalOpen, openModal, closeModal] = useBooleanState();
  const [replacementValueFilter, setReplacementValueFilter] = useState<ReplacementValueFilter>(
    getDefaultReplacementValueFilter()
  );

  const [attributeOptions, totalItems] = useAttributeOptions(
    attributeCode,
    replacementValueFilter.searchValue,
    replacementValueFilter.page,
    replacementValueFilter.codesToInclude,
    replacementValueFilter.codesToExclude
  );

  const handleConfirm = (mapping: ReplacementValues) => {
    const newOperation = {...operation, mapping};

    onOperationChange(isDefaultReplacementOperation(newOperation) ? undefined : newOperation);
    closeModal();
  };

  return (
    <Collapse
      collapseButtonLabel={isReplacementCollapsed ? translate('pim_common.close') : translate('pim_common.open')}
      label={
        <>
          {translate('akeneo.tailored_export.column_details.sources.operation.replacement.title')}
          {0 === validationErrors.length && !isDefaultReplacementOperation(operation) && <Pill level="primary" />}
          {0 < validationErrors.length && <Pill level="danger" />}
        </>
      }
      isOpen={isReplacementCollapsed}
      onCollapse={toggleReplacementCollapse}
    >
      <EditMappingButton ghost={true} level="secondary" onClick={openModal}>
        {translate('akeneo.tailored_export.column_details.sources.operation.replacement.edit_mapping')}
      </EditMappingButton>
      {isModalOpen && (
        <ReplacementModal
          replacementValueFilter={replacementValueFilter}
          onReplacementValueFilterChange={setReplacementValueFilter}
          values={attributeOptions}
          itemsPerPage={OPTION_COLLECTION_PAGE_SIZE}
          totalItems={totalItems}
          initialMapping={operation.mapping}
          validationErrors={validationErrors}
          onConfirm={handleConfirm}
          onCancel={closeModal}
        />
      )}
    </Collapse>
  );
};

export {MultiSelectReplacement};
