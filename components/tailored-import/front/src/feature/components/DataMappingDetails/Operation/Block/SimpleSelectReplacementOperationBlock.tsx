import React, {useState} from 'react';
import {Block, Button, CloseIcon, IconButton, useBooleanState, uuid} from 'akeneo-design-system';
import {DeleteModal, useTranslate} from '@akeneo-pim-community/shared';
import {isReplacementValues, ReplacementValues} from '../../../../models';
import {OPTION_COLLECTION_PAGE_SIZE, useAttributeOptions} from '../../../../hooks';
import {OperationBlockProps} from './OperationBlockProps';
import {getDefaultReplacementValueFilter, ReplacementModal, ReplacementValueFilter} from '../ReplacementModal';

const SIMPLE_SELECT_REPLACEMENT_OPERATION_TYPE = 'simple_select_replacement';

type SimpleSelectReplacementOperation = {
  uuid: string;
  type: typeof SIMPLE_SELECT_REPLACEMENT_OPERATION_TYPE;
  mapping: ReplacementValues;
};

const isSimpleSelectReplacementOperation = (operation?: any): operation is SimpleSelectReplacementOperation =>
  undefined !== operation &&
  'type' in operation &&
  SIMPLE_SELECT_REPLACEMENT_OPERATION_TYPE === operation.type &&
  'mapping' in operation &&
  isReplacementValues(operation.mapping);

const getDefaultSimpleSelectReplacementOperation = (): SimpleSelectReplacementOperation => ({
  uuid: uuid(),
  type: SIMPLE_SELECT_REPLACEMENT_OPERATION_TYPE,
  mapping: {},
});

const SimpleSelectReplacementOperationBlock = ({targetCode, operation, onChange, onRemove}: OperationBlockProps) => {
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

  if (!isSimpleSelectReplacementOperation(operation)) {
    throw new Error('SimpleSelectReplacementOperationBlock can only be used with SimpleSelectReplacementOperation');
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
      title={translate(`akeneo.tailored_import.data_mapping.operations.simple_select_replacement.title`)}
      actions={
        <>
          <Button level="tertiary" ghost={true} size="small" onClick={openReplacementModal}>
            {translate('pim_common.edit')}
          </Button>
          {isReplacementModalOpen && (
            <ReplacementModal
              title={translate('akeneo.tailored_import.data_mapping.operations.replacement.modal.options')}
              replacedValuesHeader={translate(
                'akeneo.tailored_import.data_mapping.operations.simple_select_replacement.option_labels'
              )}
              replacementValueFilter={replacementValueFilter}
              onReplacementValueFilterChange={setReplacementValueFilter}
              values={attributeOptions}
              itemsPerPage={OPTION_COLLECTION_PAGE_SIZE}
              totalItems={totalItems}
              operationType={SIMPLE_SELECT_REPLACEMENT_OPERATION_TYPE}
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
  SimpleSelectReplacementOperationBlock,
  getDefaultSimpleSelectReplacementOperation,
  SIMPLE_SELECT_REPLACEMENT_OPERATION_TYPE,
};
export type {SimpleSelectReplacementOperation};
