import React, {useState} from 'react';
import {Block, Button, CloseIcon, IconButton, useBooleanState, uuid} from 'akeneo-design-system';
import {DeleteModal, useTranslate} from '@akeneo-pim-community/shared';
import {isReplacementValues, ReplacementValues} from '../../../../models';
import {FAMILY_PAGE_SIZE, useFamilies} from '../../../../hooks';
import {OperationBlockProps} from './OperationBlockProps';
import {getDefaultReplacementValueFilter, ReplacementModal, ReplacementValueFilter} from '../ReplacementModal';

const FAMILY_REPLACEMENT_OPERATION_TYPE = 'family_replacement';

type FamilyReplacementOperation = {
  uuid: string;
  type: typeof FAMILY_REPLACEMENT_OPERATION_TYPE;
  mapping: ReplacementValues;
};

const isFamilyReplacementOperation = (operation?: any): operation is FamilyReplacementOperation =>
  undefined !== operation &&
  'type' in operation &&
  FAMILY_REPLACEMENT_OPERATION_TYPE === operation.type &&
  'mapping' in operation &&
  isReplacementValues(operation.mapping);

const getDefaultFamilyReplacementOperation = (): FamilyReplacementOperation => ({
  uuid: uuid(),
  type: FAMILY_REPLACEMENT_OPERATION_TYPE,
  mapping: {},
});

const FamilyReplacementOperationBlock = ({targetCode, operation, onChange, onRemove}: OperationBlockProps) => {
  const translate = useTranslate();
  const [isDeleteModalOpen, openDeleteModal, closeDeleteModal] = useBooleanState(false);
  const [isReplacementModalOpen, openReplacementModal, closeReplacementModal] = useBooleanState(false);

  const [replacementValueFilter, setReplacementValueFilter] = useState<ReplacementValueFilter>(
    getDefaultReplacementValueFilter()
  );

  const [families, totalItems] = useFamilies(
    replacementValueFilter.searchValue,
    replacementValueFilter.page,
    replacementValueFilter.codesToInclude,
    replacementValueFilter.codesToExclude,
    isReplacementModalOpen
  );

  if (!isFamilyReplacementOperation(operation)) {
    throw new Error('FamilyReplacementOperationBlock can only be used with FamilyReplacementOperation');
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
      title={translate(`akeneo.tailored_import.data_mapping.operations.families_replacement.title`)}
      actions={
        <>
          <Button level="tertiary" ghost={true} size="small" onClick={openReplacementModal}>
            {translate('pim_common.edit')}
          </Button>
          {isReplacementModalOpen && (
            <ReplacementModal
              title={translate('akeneo.tailored_import.data_mapping.operations.families_replacement.modal.title')}
              replacementValueFilter={replacementValueFilter}
              onReplacementValueFilterChange={setReplacementValueFilter}
              values={families}
              itemsPerPage={FAMILY_PAGE_SIZE}
              totalItems={totalItems}
              operationType={FAMILY_REPLACEMENT_OPERATION_TYPE}
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
  FamilyReplacementOperationBlock,
  getDefaultFamilyReplacementOperation,
  FAMILY_REPLACEMENT_OPERATION_TYPE,
};
export type {FamilyReplacementOperation};
