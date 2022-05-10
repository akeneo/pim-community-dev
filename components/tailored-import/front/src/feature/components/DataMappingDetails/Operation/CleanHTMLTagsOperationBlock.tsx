import React from 'react';
import {Block, CloseIcon, IconButton, useBooleanState} from 'akeneo-design-system';
import {DeleteModal, useTranslate} from '@akeneo-pim-community/shared';
import {OperationBlockProps} from './OperationBlockProps';

const CLEAN_HTML_TAGS_OPERATION_TYPE = 'clean_html_tags';

type CleanHTMLTagsOperation = {
  type: typeof CLEAN_HTML_TAGS_OPERATION_TYPE;
};

const getDefaultCleanHTMLTagsOperation = (): CleanHTMLTagsOperation => ({
  type: CLEAN_HTML_TAGS_OPERATION_TYPE,
});

const CleanHTMLTagsOperationBlock = ({operation, onRemove}: OperationBlockProps) => {
  const translate = useTranslate();
  const [isDeleteModalOpen, openDeleteModal, closeDeleteModal] = useBooleanState(false);

  return (
    <Block
      title={translate(`akeneo.tailored_import.data_mapping.operations.${operation.type}.title`)}
      actions={
        <>
          <IconButton
            title={translate('pim_common.remove')}
            icon={<CloseIcon />}
            onClick={openDeleteModal}
            ghost
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
  );
};

export {CLEAN_HTML_TAGS_OPERATION_TYPE, CleanHTMLTagsOperationBlock, getDefaultCleanHTMLTagsOperation};
export type {CleanHTMLTagsOperation};
