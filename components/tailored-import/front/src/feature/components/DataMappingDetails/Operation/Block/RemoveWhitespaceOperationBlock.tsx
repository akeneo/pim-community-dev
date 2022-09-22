import React, {useState} from 'react';
import styled from 'styled-components';
import {Block, Button, IconButton, useBooleanState, CloseIcon, Checkbox, uuid} from 'akeneo-design-system';
import {useTranslate, DeleteModal} from '@akeneo-pim-community/shared';
import {OperationBlockProps} from './OperationBlockProps';
import {OperationPreviewData} from '../OperationPreviewData';
import {Operation} from '../../../../models';

const SpacedCheckbox = styled(Checkbox)`
  margin: 0 0 20px;
`;

const REMOVE_WHITESPACE_OPERATION_TYPE = 'remove_whitespace';

const availableModes = ['consecutive', 'trim'];

type Mode = typeof availableModes[number];

type RemoveWhitespaceOperation = {
  type: typeof REMOVE_WHITESPACE_OPERATION_TYPE;
  uuid: string;
  modes: Mode[];
};

const getDefaultRemoveWhitespaceOperation = (): RemoveWhitespaceOperation => ({
  type: REMOVE_WHITESPACE_OPERATION_TYPE,
  uuid: uuid(),
  modes: ['trim'],
});

const isRemoveWhitespaceOperation = (operation: Operation): operation is RemoveWhitespaceOperation =>
  operation.type === REMOVE_WHITESPACE_OPERATION_TYPE;

const RemoveWhitespaceOperationBlock = ({
  operation,
  previewData,
  isLastOperation,
  onChange,
  onRemove,
}: OperationBlockProps) => {
  const translate = useTranslate();
  const [isDeleteModalOpen, openDeleteModal, closeDeleteModal] = useBooleanState(false);
  const [isPreviewOpen, openPreview, closePreview] = useBooleanState(isLastOperation);
  const [isBlockOpen, setBlockOpen] = useState(false);

  if (!isRemoveWhitespaceOperation(operation)) {
    throw new Error('RemoveWhitespaceOperationBlock can only be used with RemoveWhitespaceOperation');
  }

  const handleModeChange = (checked: boolean, selectedMode: Mode) => {
    if (!checked && 1 === operation.modes.length) return;

    let newModes = [...operation.modes];

    if (checked && !operation.modes.includes(selectedMode)) {
      newModes = [...newModes, selectedMode];
    }

    if (!checked && operation.modes.includes(selectedMode)) {
      newModes = newModes.filter(mode => mode !== selectedMode);
    }

    onChange({...operation, modes: newModes});
  };

  return (
    <div>
      <Block
        title={translate(`akeneo.tailored_import.data_mapping.operations.${operation.type}.title`)}
        isOpen={isBlockOpen}
        onCollapse={setBlockOpen}
        collapseButtonLabel={translate('akeneo.tailored_import.data_mapping.operations.common.collapse')}
        actions={
          <>
            <Button
              level="secondary"
              ghost={true}
              active={isPreviewOpen}
              size="small"
              onClick={isPreviewOpen ? closePreview : openPreview}
            >
              {translate('akeneo.tailored_import.data_mapping.preview.button')}
            </Button>
            <IconButton
              title={translate('pim_common.remove')}
              icon={<CloseIcon />}
              onClick={openDeleteModal}
              ghost={true}
              size="small"
              level="danger"
            />
            {isDeleteModalOpen && (
              <DeleteModal
                title={translate('akeneo.tailored_import.data_mapping.operations.title')}
                onConfirm={() => onRemove(operation.type)}
                onCancel={closeDeleteModal}
              >
                {translate('akeneo.tailored_import.data_mapping.operations.remove')}
              </DeleteModal>
            )}
          </>
        }
      >
        {availableModes.map((name: Mode) => (
          <SpacedCheckbox
            key={name}
            checked={operation.modes.includes(name)}
            onChange={(value: boolean) => handleModeChange(value, name)}
          >
            {translate(`akeneo.tailored_import.data_mapping.operations.remove_whitespace.${name}`)}
          </SpacedCheckbox>
        ))}
      </Block>
      <OperationPreviewData
        isOpen={isPreviewOpen}
        isLoading={previewData.isLoading}
        previewData={previewData.data[operation.uuid]}
        hasErrors={previewData.hasError}
      />
    </div>
  );
};

export {
  REMOVE_WHITESPACE_OPERATION_TYPE,
  getDefaultRemoveWhitespaceOperation,
  RemoveWhitespaceOperationBlock,
  isRemoveWhitespaceOperation,
};
export type {RemoveWhitespaceOperation};
