import React, {useState} from 'react';
import {Block, Button, IconButton, useBooleanState, CloseIcon, Field, SelectInput, uuid} from 'akeneo-design-system';
import {useTranslate, DeleteModal} from '@akeneo-pim-community/shared';
import {OperationBlockProps} from './OperationBlockProps';
import {OperationPreviewData} from '../OperationPreviewData';
import {Operation} from '../../../../models';

const CHANGE_CASE_OPERATION_TYPE = 'change_case';

const availableCaseModes = ['uppercase', 'lowercase', 'capitalize'];

type CaseMode = typeof availableCaseModes[number];

type ChangeCaseOperation = {
  type: typeof CHANGE_CASE_OPERATION_TYPE;
  uuid: string;
  mode: CaseMode;
};

const getDefaultChangeCaseOperation = (): ChangeCaseOperation => ({
  type: CHANGE_CASE_OPERATION_TYPE,
  uuid: uuid(),
  mode: 'uppercase',
});

const isChangeCaseOperation = (operation: Operation): operation is ChangeCaseOperation =>
  operation.type === CHANGE_CASE_OPERATION_TYPE && availableCaseModes.includes(operation.mode);

const ChangeCaseOperationBlock = ({
  operation,
  previewData,
  isLastOperation,
  onChange,
  onRemove,
}: OperationBlockProps) => {
  const translate = useTranslate();
  const [isDeleteModalOpen, openDeleteModal, closeDeleteModal] = useBooleanState(false);
  const [isPreviewOpen, openPreview, closePreview] = useBooleanState(isLastOperation);
  const [isBlockOpen, setBlockOpen] = useState(false);

  if (!isChangeCaseOperation(operation)) {
    throw new Error('ChangeCaseOperationBlock can only be used with ChangeCaseOperation');
  }

  const handleModeChange = (mode: string) => onChange({...operation, mode});

  return (
    <div>
      <Block
        title={translate(`akeneo.tailored_import.data_mapping.operations.${operation.type}.title`)}
        isOpen={isBlockOpen}
        onCollapse={setBlockOpen}
        collapseButtonLabel={translate('akeneo.tailored_import.data_mapping.operations.common.collapse')}
        actions={
          <>
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
      >
        <Field label={translate('akeneo.tailored_import.data_mapping.operations.change_case.select')}>
          <SelectInput
            emptyResultLabel={translate('pim_common.no_result')}
            value={operation.mode}
            clearable={false}
            onChange={handleModeChange}
            openLabel={translate('pim_common.open')}
            title={translate('akeneo.tailored_import.data_mapping.operations.change_case.select')}
          >
            {availableCaseModes.map((name: string) => (
              <SelectInput.Option key={name} title={name} value={name}>
                {translate(`akeneo.tailored_import.data_mapping.operations.change_case.${name}`)}
              </SelectInput.Option>
            ))}
          </SelectInput>
        </Field>
      </Block>
      <OperationPreviewData
        isOpen={isPreviewOpen}
        isLoading={previewData.isLoading}
        previewData={previewData.data[operation.uuid]}
        hasErrors={previewData.hasError}
      />
    </div>
  );
};

export {CHANGE_CASE_OPERATION_TYPE, getDefaultChangeCaseOperation, ChangeCaseOperationBlock, isChangeCaseOperation};
export type {ChangeCaseOperation};
