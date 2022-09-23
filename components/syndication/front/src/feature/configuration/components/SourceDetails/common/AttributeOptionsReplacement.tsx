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
import {useAttributeOptions, OPTION_COLLECTION_PAGE_SIZE} from '../../../hooks';

const EditMappingButton = styled(Button)`
  margin: 2px 2px 10px;
`;

type AttributeOptionsReplacementProps = {
  operation?: ReplacementOperation;
  attributeCode: string;
  validationErrors: ValidationError[];
  onOperationChange: (updatedOperation?: ReplacementOperation) => void;
};

const AttributeOptionsReplacement = ({
  operation = getDefaultReplacementOperation(),
  attributeCode,
  validationErrors,
  onOperationChange,
}: AttributeOptionsReplacementProps) => {
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
          title={translate('akeneo.syndication.data_mapping_details.sources.operation.replacement.modal.options')}
          replacementValueFilter={replacementValueFilter}
          onReplacementValueFilterChange={setReplacementValueFilter}
          values={attributeOptions}
          itemsPerPage={OPTION_COLLECTION_PAGE_SIZE}
          totalItems={totalItems}
          initialMapping={operation.mapping}
          validationErrors={validationErrors}
          onConfirm={handleConfirm}
          onCancel={handleCancel}
        />
      )}
    </Collapse>
  );
};

export {AttributeOptionsReplacement};
