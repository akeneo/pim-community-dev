import React, {useState} from 'react';
import {isReplacementValues, ReplacementValues} from '../../../../models';
import {Block, Button, CloseIcon, IconButton, useBooleanState, uuid} from 'akeneo-design-system';
import {OperationBlockProps} from './OperationBlockProps';
import {DeleteModal, useTranslate} from '@akeneo-pim-community/shared';
import {getDefaultReplacementValueFilter, ReplacementModal, ReplacementValueFilter} from '../ReplacementModal';
import {RECORDS_COLLECTION_PAGE_SIZE, useRecords} from '../../../../hooks';

const SIMPLE_REFERENCE_ENTITY_REPLACEMENT_OPERATION_TYPE = 'simple_reference_entity_replacement';

type SimpleReferenceEntityReplacementOperation = {
  uuid: string;
  type: typeof SIMPLE_REFERENCE_ENTITY_REPLACEMENT_OPERATION_TYPE;
  mapping: ReplacementValues;
};

const isSimpleReferenceEntityReplacement = (operation?: any): operation is SimpleReferenceEntityReplacementOperation =>
  undefined !== operation &&
  'type' in operation &&
  SIMPLE_REFERENCE_ENTITY_REPLACEMENT_OPERATION_TYPE === operation.type &&
  'mapping' in operation &&
  isReplacementValues(operation.mapping);

const getDefaultSimpleReferenceEntityReplacementOperation = (): SimpleReferenceEntityReplacementOperation => ({
  uuid: uuid(),
  type: SIMPLE_REFERENCE_ENTITY_REPLACEMENT_OPERATION_TYPE,
  mapping: {},
});

const SimpleReferenceEntityReplacementOperationBlock = ({
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

  if (!isSimpleReferenceEntityReplacement(operation)) {
    throw new Error(
      'SimpleReferenceEntityReplacementOperationBlock can only be used with SimpleReferenceEntityReplacementOperation'
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
      title={translate(`akeneo.tailored_import.data_mapping.operations.simple_reference_entity_replacement.title`)}
      actions={
        <>
          <Button level="tertiary" ghost={true} size="small" onClick={openReplacementModal}>
            {translate('pim_common.edit')}
          </Button>
          {isReplacementModalOpen && (
            <ReplacementModal
              title={translate('akeneo.tailored_import.data_mapping.operations.replacement.modal.records')}
              replacedValuesHeader={translate(
                'akeneo.tailored_import.data_mapping.operations.simple_reference_entity_replacement.option_labels'
              )}
              replacementValueFilter={replacementValueFilter}
              onReplacementValueFilterChange={setReplacementValueFilter}
              values={records}
              itemsPerPage={RECORDS_COLLECTION_PAGE_SIZE}
              totalItems={totalItems}
              operationType={SIMPLE_REFERENCE_ENTITY_REPLACEMENT_OPERATION_TYPE}
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
  SimpleReferenceEntityReplacementOperationBlock,
  getDefaultSimpleReferenceEntityReplacementOperation,
  SIMPLE_REFERENCE_ENTITY_REPLACEMENT_OPERATION_TYPE,
};
export type {SimpleReferenceEntityReplacementOperation};
