import React, {forwardRef, SyntheticEvent} from 'react';
import styled from 'styled-components';
import {CloseIcon, Helper, IconButton, List, TextInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {ColumnConfiguration} from '../../models/ColumnConfiguration';
import {useValidationErrors} from '../../contexts';

const Field = styled.div`
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 5px;
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

    const handleColumnRemove = (event: SyntheticEvent, uuid: string) => {
      event.stopPropagation();
      onColumnRemoved(uuid);
      onFocusNext(uuid);
    };

    const targetErrors = useValidationErrors(`[columns][${column.uuid}][target]`, true);

    return (
      <List.Row key={column.uuid} onClick={() => onColumnSelected(column.uuid)} selected={isSelected}>
        <List.Cell width={300}>
          <Field>
            <TextInput
              ref={ref}
              onChange={updatedValue => onColumnChange({...column, target: updatedValue})}
              onSubmit={() => onFocusNext(column.uuid)}
              placeholder={translate('akeneo.tailored_export.column_list.column_row.target_placeholder')}
              value={column.target}
            />
            {targetErrors.map((error, index) => (
              <Helper key={index} inline={true} level="error">
                {translate(error.messageTemplate, error.parameters)}
              </Helper>
            ))}
          </Field>
        </List.Cell>
        <List.Cell width="auto">{translate('akeneo.tailored_export.column_list.column_row.no_source')}</List.Cell>
        <List.RemoveCell>
          <IconButton
            ghost="borderless"
            level="tertiary"
            icon={<CloseIcon />}
            title={translate('akeneo.tailored_export.column_list.column_row.remove')}
            onClick={event => handleColumnRemove(event, column.uuid)}
          />
        </List.RemoveCell>
      </List.Row>
    );
  }
);

export {ColumnRow};
