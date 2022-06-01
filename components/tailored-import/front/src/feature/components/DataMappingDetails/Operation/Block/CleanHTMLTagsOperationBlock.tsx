import React from 'react';
import {Block, Button, CloseIcon, IconButton, useBooleanState} from 'akeneo-design-system';
import {DeleteModal, useTranslate} from '@akeneo-pim-community/shared';
import {OperationBlockProps} from './OperationBlockProps';
import {OperationPreviewData} from '../OperationPreviewData';

const CLEAN_HTML_TAGS_OPERATION_TYPE = 'clean_html_tags';

type CleanHTMLTagsOperation = {
  type: typeof CLEAN_HTML_TAGS_OPERATION_TYPE;
};

const getDefaultCleanHTMLTagsOperation = (): CleanHTMLTagsOperation => ({
  type: CLEAN_HTML_TAGS_OPERATION_TYPE,
});

const CleanHTMLTagsOperationBlock = ({operation, previewData, isLastOperation, onRemove}: OperationBlockProps) => {
  const translate = useTranslate();
  const [isDeleteModalOpen, openDeleteModal, closeDeleteModal] = useBooleanState(false);
  const [isPreviewOpen, openPreview, closePreview] = useBooleanState(isLastOperation);

  return (
    <div>
      <Block
        title={translate(`akeneo.tailored_import.data_mapping.operations.${operation.type}.title`)}
        actions={
          <>
            <Button level="secondary" ghost={true} size="small" onClick={isPreviewOpen ? closePreview : openPreview}>
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
      />
      <OperationPreviewData
        isOpen={isPreviewOpen}
        isLoading={previewData.isLoading}
        previewData={previewData.data}
        hasErrors={previewData.hasError}
      />
    </div>
  );
};

export {CLEAN_HTML_TAGS_OPERATION_TYPE, CleanHTMLTagsOperationBlock, getDefaultCleanHTMLTagsOperation};
export type {CleanHTMLTagsOperation};
