import React from 'react';
import {Block, Button, CloseIcon, IconButton, useBooleanState, uuid} from 'akeneo-design-system';
import {DeleteModal, useTranslate} from '@akeneo-pim-community/shared';
import {OperationBlockProps} from '../OperationBlockProps';
import {OperationPreviewData} from '../../OperationPreviewData';
import {Operation, SearchAndReplaceValue} from '../../../../../models';
import {SearchAndReplaceModal} from './SearchAndReplaceModal';

const SEARCH_AND_REPLACE_OPERATION_TYPE = 'search_and_replace';

type SearchAndReplaceOperation = {
  uuid: string;
  type: typeof SEARCH_AND_REPLACE_OPERATION_TYPE;
  replacements: SearchAndReplaceValue[];
};

const getDefaultSearchAndReplaceOperation = (): SearchAndReplaceOperation => ({
  uuid: uuid(),
  type: SEARCH_AND_REPLACE_OPERATION_TYPE,
  replacements: [],
});

const isSearchAndReplaceOperation = (operation: Operation): operation is SearchAndReplaceOperation =>
  operation.type === SEARCH_AND_REPLACE_OPERATION_TYPE;

const SearchAndReplaceOperationBlock = ({
  operation,
  previewData,
  isLastOperation,
  onChange,
  onRemove,
}: OperationBlockProps) => {
  const translate = useTranslate();
  const [isDeleteModalOpen, openDeleteModal, closeDeleteModal] = useBooleanState(false);
  const [isPreviewOpen, openPreview, closePreview] = useBooleanState(isLastOperation);
  const [isReplacementModalOpen, openReplacementModal, closeReplacementModal] = useBooleanState(false);

  if (!isSearchAndReplaceOperation(operation)) {
    throw new Error('SearchAndReplaceOperationBlock can only be used with SearchAndReplaceOperation');
  }

  const handleChange = (replacements: SearchAndReplaceValue[]) => {
    onChange({...operation, replacements});
    closeReplacementModal();
  };

  return (
    <div>
      <Block
        title={translate(`akeneo.tailored_import.data_mapping.operations.${operation.type}.title`)}
        actions={
          <>
            <Button level="tertiary" ghost={true} size="small" onClick={openReplacementModal}>
              {translate('pim_common.edit')}
            </Button>
            {isReplacementModalOpen && (
              <SearchAndReplaceModal
                operationUuid={operation.uuid}
                initialReplacements={operation.replacements}
                onConfirm={handleChange}
                onCancel={closeReplacementModal}
              />
            )}
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
      />
      <OperationPreviewData
        isOpen={isPreviewOpen}
        isLoading={previewData.isLoading}
        previewData={previewData.data[operation.uuid]}
        hasErrors={previewData.hasError}
      />
    </div>
  );
};

export {SEARCH_AND_REPLACE_OPERATION_TYPE, SearchAndReplaceOperationBlock, getDefaultSearchAndReplaceOperation};
export type {SearchAndReplaceOperation, SearchAndReplaceValue};
