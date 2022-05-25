import React, {useState} from 'react';
import {Block, Button, CloseIcon, IconButton, useBooleanState} from 'akeneo-design-system';
import {DeleteModal, useTranslate} from '@akeneo-pim-community/shared';
import {OperationBlockProps} from './OperationBlockProps';
import {isReplacementValues, ReplacementValues} from './ReplacementValues';
import {getDefaultReplacementValueFilter, ReplacementModal, ReplacementValueFilter} from './ReplacementModal';
import {OPTION_COLLECTION_PAGE_SIZE, useAttributeOptions} from '../../../hooks';

const MULTI_SELECT_REPLACEMENT_OPERATION_TYPE = 'multi_select_replacement';

type MultiSelectReplacementOperation = {
  type: typeof MULTI_SELECT_REPLACEMENT_OPERATION_TYPE;
  mapping: ReplacementValues;
};

const isMultiSelectReplacementOperation = (operation?: any): operation is MultiSelectReplacementOperation =>
  undefined !== operation &&
  'type' in operation &&
  MULTI_SELECT_REPLACEMENT_OPERATION_TYPE === operation.type &&
  'mapping' in operation &&
  isReplacementValues(operation.mapping);

const getDefaultMultiSelectReplacementOperation = (): MultiSelectReplacementOperation => ({
  type: MULTI_SELECT_REPLACEMENT_OPERATION_TYPE,
  mapping: {},
});

const MultiSelectReplacementOperationBlock = ({targetCode, operation, onChange, onRemove}: OperationBlockProps) => {
  const translate = useTranslate();
  const [isDeleteModalOpen, openDeleteModal, closeDeleteModal] = useBooleanState(false);
  const [isReplacementModalOpen, openReplacementModal, closeReplacementModal] = useBooleanState(false);

  const [replacementValueFilter, setReplacementValueFilter] = useState<ReplacementValueFilter>(
    getDefaultReplacementValueFilter()
  );

  const [attributeOptions, totalItems] = useAttributeOptions(
    targetCode,
    replacementValueFilter.searchValue,
    replacementValueFilter.page,
    replacementValueFilter.codesToInclude,
    replacementValueFilter.codesToExclude,
    isReplacementModalOpen
  );

  if (!isMultiSelectReplacementOperation(operation)) {
    throw new Error('MultiSelectReplacementOperationBlock can only be used with MultiSelectReplacementOperation');
  }

  const handleCancel = () => {
    closeReplacementModal();
    setReplacementValueFilter(getDefaultReplacementValueFilter());
  };

  const handleConfirm = (mapping: ReplacementValues) => {
    const newOperation = {...operation, mapping};

    onChange(newOperation);
    closeReplacementModal();
    setReplacementValueFilter(getDefaultReplacementValueFilter());
  };

  return (
    <Block
      title={translate(`akeneo.tailored_import.data_mapping.operations.multi_select_replacement.title`)}
      actions={
        <>
          <Button level="tertiary" ghost={true} size="small" onClick={openReplacementModal}>
            {translate('pim_common.edit')}
          </Button>
          {isReplacementModalOpen && (
            <ReplacementModal
              title={translate('akeneo.tailored_import.data_mapping.operations.replacement.modal.options')}
              replacementValueFilter={replacementValueFilter}
              onReplacementValueFilterChange={setReplacementValueFilter}
              values={attributeOptions}
              itemsPerPage={OPTION_COLLECTION_PAGE_SIZE}
              totalItems={totalItems}
              operationType={MULTI_SELECT_REPLACEMENT_OPERATION_TYPE}
              initialMapping={operation.mapping}
              onConfirm={handleConfirm}
              onCancel={handleCancel}
            />
          )}
          <IconButton
            title={translate('pim_common.remove')}
            icon={<CloseIcon />}
            ghost={true}
            level="danger"
            size="small"
            onClick={openDeleteModal}
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

export {
  MultiSelectReplacementOperationBlock,
  getDefaultMultiSelectReplacementOperation,
  MULTI_SELECT_REPLACEMENT_OPERATION_TYPE,
};
export type {MultiSelectReplacementOperation};
