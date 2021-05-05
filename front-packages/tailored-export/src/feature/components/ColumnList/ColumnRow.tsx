import React, {forwardRef, SyntheticEvent} from 'react';
import styled from 'styled-components';
import {CloseIcon, getColor, Helper, IconButton, List, TextInput, useBooleanState} from 'akeneo-design-system';
import {DeleteModal, useTranslate} from '@akeneo-pim-community/shared';
import {ColumnConfiguration} from '../../models/ColumnConfiguration';
import {useValidationErrors} from '../../contexts';

const Field = styled.div`
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 5px;
`;

const SourceList = styled.div`
  color: ${getColor('grey', 100)};
  font-style: italic;
  margin-left: 20px;
`;

type ColumnRowProps = {
  column: ColumnConfiguration;
  isSelected: boolean;
  onColumnChange: (column: ColumnConfiguration) => void;
  onColumnSelected: (uuid: string | null) => void;
  onColumnRemoved: (uuid: string) => void;
  onFocusNext: (uuid: string) => void;
};

const ColumnRow = forwardRef<HTMLInputElement, ColumnRowProps>(
  ({column, isSelected, onColumnChange, onFocusNext, onColumnSelected, onColumnRemoved}: ColumnRowProps, ref) => {
    const translate = useTranslate();
    const [isDeleteModalOpen, openDeleteModal, closeDeleteModal] = useBooleanState();

    const handleColumnRemove = (event: SyntheticEvent) => {
      event.stopPropagation();
      openDeleteModal();
    };

    const handleConfirmColumnRemove = () => {
      onColumnRemoved(column.uuid);
      onFocusNext(column.uuid);
    };

    const targetErrors = useValidationErrors(`[columns][${column.uuid}][target]`, true);

    return (
      <>
        <List.Row
          key={column.uuid}
          onClick={() => onColumnSelected(column.uuid)}
          isSelected={isSelected}
          isMultiline={targetErrors.length !== 0}
        >
          <List.Cell width={300}>
            <Field>
              <TextInput
                ref={ref}
                onChange={updatedValue => onColumnChange({...column, target: updatedValue})}
                onSubmit={() => onFocusNext(column.uuid)}
                invalid={targetErrors.length !== 0}
                value={column.target}
              />
              {targetErrors.map((error, index) => (
                <Helper key={index} inline={true} level="error">
                  {translate(error.messageTemplate, error.parameters)}
                </Helper>
              ))}
            </Field>
          </List.Cell>
          <List.Cell width="auto">
            <SourceList>{translate('akeneo.tailored_export.column_list.column_row.no_source')}</SourceList>
          </List.Cell>
          <List.RemoveCell>
            <IconButton
              ghost="borderless"
              level="tertiary"
              icon={<CloseIcon />}
              title={translate('akeneo.tailored_export.column_list.column_row.remove')}
              onClick={handleColumnRemove}
            />
          </List.RemoveCell>
        </List.Row>
        {isDeleteModalOpen && (
          <DeleteModal
            title={translate('akeneo.tailored_export.column_list.title')}
            onConfirm={handleConfirmColumnRemove}
            onCancel={closeDeleteModal}
          >
            {translate('akeneo.tailored_export.column.delete_message')}
          </DeleteModal>
        )}
      </>
    );
  }
);

export {ColumnRow};
