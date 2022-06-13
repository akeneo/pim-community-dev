import React from 'react';
import {Block, Button, CloseIcon, IconButton, useBooleanState} from 'akeneo-design-system';
import {DeleteModal, useTranslate} from '@akeneo-pim-community/shared';
import {isReplacementValues, ReplacementValues} from '../../../../models';
import {OperationBlockProps} from './OperationBlockProps';
import {CategoryReplacementModal} from "../CategoryReplacementModal/CategoryReplacementModal";

const CATEGORY_REPLACEMENT_OPERATION_TYPE = 'category_replacement';

type CategoryReplacementOperation = {
  // TODO: don't forget to add uuid
  type: typeof CATEGORY_REPLACEMENT_OPERATION_TYPE;
  mapping: ReplacementValues;
};

const isCategoryReplacementOperation = (operation?: any): operation is CategoryReplacementOperation =>
  undefined !== operation &&
  'type' in operation &&
  CATEGORY_REPLACEMENT_OPERATION_TYPE === operation.type &&
  'mapping' in operation &&
  isReplacementValues(operation.mapping);

const getDefaultCategoryReplacementOperation = (): CategoryReplacementOperation => ({
  // TODO: don't forget to add uuid
  type: CATEGORY_REPLACEMENT_OPERATION_TYPE,
  mapping: {},
});

const CategoryReplacementOperationBlock = ({targetCode, operation, onChange, onRemove}: OperationBlockProps) => {
  const translate = useTranslate();
  const [isDeleteModalOpen, openDeleteModal, closeDeleteModal] = useBooleanState(false);
  const [isReplacementModalOpen, openReplacementModal, closeReplacementModal] = useBooleanState(false);

  if (!isCategoryReplacementOperation(operation)) {
    throw new Error('CategoryReplacementOperationBlock can only be used with CategoryReplacementOperation');
  }

  const handleCancel = () => {
    closeReplacementModal();
  };

  const handleConfirm = (mapping: ReplacementValues) => {
    const newOperation = {...operation, mapping};

    onChange(newOperation);
    closeReplacementModal();
  };

  return (
    <Block
      title={translate(`akeneo.tailored_import.data_mapping.operations.category_replacement.title`)}
      actions={
        <>
          <Button level="tertiary" ghost={true} size="small" onClick={openReplacementModal}>
            {translate('pim_common.edit')}
          </Button>
          {isReplacementModalOpen && (
            <CategoryReplacementModal
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
  CategoryReplacementOperationBlock,
  getDefaultCategoryReplacementOperation,
  CATEGORY_REPLACEMENT_OPERATION_TYPE,
};
export type {CategoryReplacementOperation};
