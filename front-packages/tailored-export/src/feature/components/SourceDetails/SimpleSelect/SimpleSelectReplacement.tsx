import React, {useState} from 'react';
import styled from 'styled-components';
import {Button, Collapse, Pill, useBooleanState} from 'akeneo-design-system';
import {useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {
  ReplacementOperation,
  getDefaultReplacementOperation,
  isDefaultReplacementOperation,
} from '../common/ReplacementOperation';
import {Attribute} from '../../../models';
import {ReplaceValueFilter, ReplacementModal} from './ReplacementModal';
import {ReplacementValues} from '../common';
import {useAttributeOptions} from '../../../hooks/useAttributeOptions';

const EditMappingButton = styled(Button)`
  margin: 2px 2px 10px;
`;

type SimpleSelectReplacementProps = {
  operation?: ReplacementOperation;
  attribute: Attribute;
  validationErrors: ValidationError[];
  onOperationChange: (updatedOperation?: ReplacementOperation) => void;
};

const SimpleSelectReplacement = ({
  operation = getDefaultReplacementOperation(),
  attribute,
  validationErrors,
  onOperationChange,
}: SimpleSelectReplacementProps) => {
  const translate = useTranslate();
  const [isReplacementCollapsed, toggleReplacementCollapse] = useState<boolean>(false);
  const [isModalOpen, openModal, closeModal] = useBooleanState();
  const [replaceValueFilter, setReplaceValueFilter] = useState<ReplaceValueFilter>({
    searchValue: '',
    page: 1,
    codesToInclude: [],
    codesToExclude: [],
  });

  const [attributeOptions, totalItems] = useAttributeOptions(
    attribute.code,
    replaceValueFilter.searchValue,
    replaceValueFilter.page,
    replaceValueFilter.codesToInclude,
    replaceValueFilter.codesToExclude
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
          replaceValueFilter={replaceValueFilter}
          onReplaceValueFilterChange={setReplaceValueFilter}
          values={attributeOptions}
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

export {SimpleSelectReplacement};
