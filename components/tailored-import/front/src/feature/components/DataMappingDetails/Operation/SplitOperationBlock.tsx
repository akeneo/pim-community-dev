import React, {useState} from 'react';
import {Block, CloseIcon, Field, IconButton, SelectInput, useBooleanState} from 'akeneo-design-system';
import {DeleteModal, useTranslate} from '@akeneo-pim-community/shared';
import {OperationBlockProps} from './OperationBlockProps';
import {Operation} from 'feature/models/Operation';

const SPLIT_OPERATION_TYPE = 'split';

type SplitOperation = {
  type: typeof SPLIT_OPERATION_TYPE;
  separator: string;
};

const getDefaultSplitOperation = (): SplitOperation => ({
  type: SPLIT_OPERATION_TYPE,
  separator: ',',
});

const availableSeparators = {',': 'comma', ';': 'semicolon', '|': 'pipe'};

const isSplitOperation = (operation: Operation): operation is SplitOperation => operation.type === SPLIT_OPERATION_TYPE;

const SplitOperationBlock = ({operation, onChange, onRemove}: OperationBlockProps) => {
  const translate = useTranslate();
  const [isDeleteModalOpen, openDeleteModal, closeDeleteModal] = useBooleanState(false);
  const [isBlockOpen, setBlockOpen] = useState(false);

  if (!isSplitOperation(operation)) {
    throw new Error('SplitOperationBlock can only be used with SplitOperation');
  }

  const handleSeparatorChange = (separator: string) => onChange({...operation, separator});

  return (
    <Block
      title={translate(`akeneo.tailored_import.data_mapping.operations.${operation.type}.title`)}
      isOpen={isBlockOpen}
      onCollapse={setBlockOpen}
      collapseButtonLabel={translate('akeneo.tailored_import.data_mapping.operations.split.collapse')}
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
    >
      <Field label={translate('akeneo.tailored_import.data_mapping.operations.split.separator')}>
        <SelectInput
          emptyResultLabel={translate('pim_common.no_result')}
          value={operation.separator}
          clearable={false}
          onChange={handleSeparatorChange}
          openLabel={translate('pim_common.open')}
          title={translate('akeneo.tailored_import.data_mapping.operations.split.separator')}
        >
          {Object.entries(availableSeparators).map(([separator, name]) => (
            <SelectInput.Option key={separator} title={name} value={separator}>
              {translate(`akeneo.tailored_import.data_mapping.operations.split.${name}`)}
            </SelectInput.Option>
          ))}
        </SelectInput>
      </Field>
    </Block>
  );
};

export {SPLIT_OPERATION_TYPE, SplitOperationBlock, getDefaultSplitOperation};
export type {SplitOperation};
