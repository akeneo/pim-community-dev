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
import {ReplacementModal} from './ReplacementModal';
import {ReplacementValues} from '../common';

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

  const handleConfirm = (mapping: ReplacementValues) => {
    onOperationChange({...operation, mapping: mapping});
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
          initialMapping={operation.mapping}
          attribute={attribute}
          validationErrors={validationErrors}
          onConfirm={handleConfirm}
          onCancel={closeModal}
        />
      )}
    </Collapse>
  );
};

export {SimpleSelectReplacement};
