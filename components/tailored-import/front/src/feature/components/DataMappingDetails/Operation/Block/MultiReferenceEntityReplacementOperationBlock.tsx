import React, {useState} from 'react';
import {Block, Button, CloseIcon, IconButton, useBooleanState, uuid} from 'akeneo-design-system';
import {DeleteModal, useTranslate} from '@akeneo-pim-community/shared';
import {isReplacementValues, ReplacementValues} from '../../../../models';
import {RECORDS_COLLECTION_PAGE_SIZE, useRecords} from '../../../../hooks';
import {OperationBlockProps} from './OperationBlockProps';
import {getDefaultReplacementValueFilter, ReplacementModal, ReplacementValueFilter} from '../ReplacementModal';

const MULTI_REFERENCE_ENTITY_REPLACEMENT_OPERATION_TYPE = 'multi_reference_entity_replacement';

type MultiReferenceEntityReplacementOperation = {
  uuid: string;
  type: typeof MULTI_REFERENCE_ENTITY_REPLACEMENT_OPERATION_TYPE;
  mapping: ReplacementValues;
};

const isMultiReferenceEntityReplacementOperation = (
  operation?: any
): operation is MultiReferenceEntityReplacementOperation =>
  undefined !== operation &&
  'type' in operation &&
  MULTI_REFERENCE_ENTITY_REPLACEMENT_OPERATION_TYPE === operation.type &&
  'mapping' in operation &&
  isReplacementValues(operation.mapping);

const getDefaultMultiReferenceEntityReplacementOperation = (): MultiReferenceEntityReplacementOperation => ({
  uuid: uuid(),
  type: MULTI_REFERENCE_ENTITY_REPLACEMENT_OPERATION_TYPE,
  mapping: {},
});

const MultiReferenceEntityReplacementOperationBlock = ({
  targetReferenceDataName,
  operation,
  onChange,
  onRemove,
}: OperationBlockProps) => {
  const translate = useTranslate();
  const [isDeleteModalOpen, openDeleteModal, closeDeleteModal] = useBooleanState(false);
  const [isReplacementModalOpen, openReplacementModal, closeReplacementModal] = useBooleanState(false);
  const [replacementValueFilter, setReplacementValueFilter] = useState<ReplacementValueFilter>(
    getDefaultReplacementValueFilter()
  );

  if (!isMultiReferenceEntityReplacementOperation(operation)) {
    throw new Error(
      'MultiReferenceEntityReplacementOperationBlock can only be used with MultiReferenceEntityReplacementOperation'
    );
  }

  if (!targetReferenceDataName) {
    throw new Error('Missing Reference Data name in attribute');
  }

  const [records, totalItems] = useRecords(
    targetReferenceDataName,
    replacementValueFilter.searchValue,
    replacementValueFilter.page,
    replacementValueFilter.codesToInclude,
    replacementValueFilter.codesToExclude,
    isReplacementModalOpen
  );

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
      title={translate(`akeneo.tailored_import.data_mapping.operations.multi_reference_entity_replacement.title`)}
      actions={
        <>
          <Button level="tertiary" ghost={true} size="small" onClick={openReplacementModal}>
            {translate('pim_common.edit')}
          </Button>
          {isReplacementModalOpen && (
            <ReplacementModal
              title={translate('akeneo.tailored_import.data_mapping.operations.replacement.modal.records')}
              replacedValuesHeader={translate(
                'akeneo.tailored_import.data_mapping.operations.multi_reference_entity_replacement.option_labels'
              )}
              replacementValueFilter={replacementValueFilter}
              onReplacementValueFilterChange={setReplacementValueFilter}
              values={records}
              itemsPerPage={RECORDS_COLLECTION_PAGE_SIZE}
              totalItems={totalItems}
              operationType={MULTI_REFERENCE_ENTITY_REPLACEMENT_OPERATION_TYPE}
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
  MultiReferenceEntityReplacementOperationBlock,
  getDefaultMultiReferenceEntityReplacementOperation,
  MULTI_REFERENCE_ENTITY_REPLACEMENT_OPERATION_TYPE,
};
export type {MultiReferenceEntityReplacementOperation};
