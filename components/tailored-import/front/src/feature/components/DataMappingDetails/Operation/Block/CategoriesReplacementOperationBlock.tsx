import React from 'react';
import {Block, Button, CloseIcon, IconButton, useBooleanState, uuid} from 'akeneo-design-system';
import {DeleteModal, useTranslate} from '@akeneo-pim-community/shared';
import {isReplacementValues, ReplacementValues} from '../../../../models';
import {OperationBlockProps} from './OperationBlockProps';
import {CategoriesReplacementModal} from '../CategoriesReplacementModal/CategoriesReplacementModal';

const CATEGORIES_REPLACEMENT_OPERATION_TYPE = 'categories_replacement';

type CategoriesReplacementOperation = {
  uuid: string;
  type: typeof CATEGORIES_REPLACEMENT_OPERATION_TYPE;
  mapping: ReplacementValues;
};

const isCategoriesReplacementOperation = (operation?: any): operation is CategoriesReplacementOperation =>
  undefined !== operation &&
  'type' in operation &&
  CATEGORIES_REPLACEMENT_OPERATION_TYPE === operation.type &&
  'mapping' in operation &&
  isReplacementValues(operation.mapping);

const getDefaultCategoriesReplacementOperation = (): CategoriesReplacementOperation => ({
  uuid: uuid(),
  type: CATEGORIES_REPLACEMENT_OPERATION_TYPE,
  mapping: {},
});

const CategoriesReplacementOperationBlock = ({operation, onChange, onRemove}: OperationBlockProps) => {
  const translate = useTranslate();
  const [isDeleteModalOpen, openDeleteModal, closeDeleteModal] = useBooleanState(false);
  const [isReplacementModalOpen, openReplacementModal, closeReplacementModal] = useBooleanState(false);

  if (!isCategoriesReplacementOperation(operation)) {
    throw new Error('CategoriesReplacementOperationBlock can only be used with CategoriesReplacementOperation');
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
      title={translate(`akeneo.tailored_import.data_mapping.operations.categories_replacement.title`)}
      actions={
        <>
          <Button level="tertiary" ghost={true} size="small" onClick={openReplacementModal}>
            {translate('pim_common.edit')}
          </Button>
          {isReplacementModalOpen && (
            <CategoriesReplacementModal
              operationUuid={operation.uuid}
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
  CategoriesReplacementOperationBlock,
  getDefaultCategoriesReplacementOperation,
  CATEGORIES_REPLACEMENT_OPERATION_TYPE,
};
export type {CategoriesReplacementOperation};
