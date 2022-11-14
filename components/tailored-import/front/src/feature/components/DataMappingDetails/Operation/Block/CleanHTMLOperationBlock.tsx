import React, {useState} from 'react';
import styled from 'styled-components';
import {Block, Button, CloseIcon, IconButton, useBooleanState, uuid, Checkbox} from 'akeneo-design-system';
import {DeleteModal, useTranslate} from '@akeneo-pim-community/shared';
import {OperationBlockProps} from './OperationBlockProps';
import {OperationPreviewData} from '../OperationPreviewData';
import {Operation} from '../../../../models/Operation';

const SpacedCheckbox = styled(Checkbox)`
  margin: 0 0 20px;
`;

const CLEAN_HTML_OPERATION_TYPE = 'clean_html';

const availableHtmlModes = ['remove', 'decode'];

type HtmlMode = typeof availableHtmlModes[number];

type CleanHTMLOperation = {
  uuid: string;
  type: typeof CLEAN_HTML_OPERATION_TYPE;
  modes: HtmlMode[];
};

const getDefaultCleanHTMLOperation = (): CleanHTMLOperation => ({
  uuid: uuid(),
  type: CLEAN_HTML_OPERATION_TYPE,
  modes: ['remove', 'decode'],
});

const isCleanHTMLOperation = (operation: Operation): operation is CleanHTMLOperation =>
  operation.type === CLEAN_HTML_OPERATION_TYPE;

const CleanHTMLOperationBlock = ({
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

  if (!isCleanHTMLOperation(operation)) {
    throw new Error('CleanHTMLOperationBlock can only be used with CleanHTMLOperation');
  }

  const handleChange = (checked: boolean, selectedMode: HtmlMode) => {
    if (!checked && 1 === operation.modes.length) return;

    let updatedModes = [...operation.modes];

    if (checked && !operation.modes.includes(selectedMode)) updatedModes = [...updatedModes, selectedMode];

    if (!checked && operation.modes.includes(selectedMode))
      updatedModes = updatedModes.filter(mode => mode !== selectedMode);

    onChange({...operation, modes: updatedModes});
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
        {availableHtmlModes.map((mode: HtmlMode) => (
          <SpacedCheckbox
            key={mode}
            checked={operation.modes.includes(mode)}
            onChange={value => handleChange(value, mode)}
          >
            {translate(`akeneo.tailored_import.data_mapping.operations.clean_html.${mode}`)}
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

export {CLEAN_HTML_OPERATION_TYPE, CleanHTMLOperationBlock, getDefaultCleanHTMLOperation};
export type {CleanHTMLOperation};
