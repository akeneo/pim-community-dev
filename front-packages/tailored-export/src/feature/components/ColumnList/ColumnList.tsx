import React, {useEffect, useRef} from 'react';
import {Helper, List, SectionTitle, TextInput, useAutoFocus} from 'akeneo-design-system';
import {ColumnConfiguration, ColumnsConfiguration} from '../../models/ColumnConfiguration';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/shared';
import {ColumnListPlaceholder} from './ColumnListPlaceholder';
import {ColumnRow} from './ColumnRow';
import {useValidationErrors} from '../../contexts';

const MAX_COLUMN_COUNT = 1000;

const Container = styled.div`
  flex: 1;
  height: 100%;
  overflow-y: auto;
`;

type ColumnListProps = {
  columnsConfiguration: ColumnsConfiguration;
  selectedColumn: ColumnConfiguration | null;
  onColumnCreated: (target: string) => void;
  onColumnChange: (column: ColumnConfiguration) => void;
  onColumnSelected: (uuid: string | null) => void;
  onColumnRemoved: (uuid: string) => void;
};

const ColumnList = ({
  columnsConfiguration,
  selectedColumn,
  onColumnCreated,
  onColumnChange,
  onColumnSelected,
  onColumnRemoved,
}: ColumnListProps) => {
  const translate = useTranslate();
  const inputRef = useRef<HTMLInputElement>(null);
  const focus = useAutoFocus(inputRef);

  useEffect(() => {
    focus();
  }, [selectedColumn, focus]);

  const handleFocusNextColumn = (columnUuid: string) => {
    const currentColumnIndex = columnsConfiguration.findIndex(({uuid}) => columnUuid === uuid);
    const nextColumn = columnsConfiguration[currentColumnIndex + 1];

    onColumnSelected(undefined === nextColumn ? null : nextColumn.uuid);
  };

  const globalErrors = useValidationErrors('[columns]', true);

  const canAddColumn = MAX_COLUMN_COUNT > columnsConfiguration.length;
  const isLastColumnFilled = 0 < columnsConfiguration.length && columnsConfiguration[columnsConfiguration.length - 1].target !== '';

  return (
    <Container>
      <SectionTitle sticky={0}>
        <SectionTitle.Title>{translate('akeneo.tailored_export.column_list.title')}</SectionTitle.Title>
        <SectionTitle.Spacer />
      </SectionTitle>
      {globalErrors.map((error, index) => (
        <Helper key={index} level="error">
          {translate(error.messageTemplate, error.parameters)}
        </Helper>
      ))}
      <List>
        {columnsConfiguration.map(column => (
          <ColumnRow
            key={column.uuid}
            ref={selectedColumn?.uuid === column.uuid ? inputRef : null}
            column={column}
            isSelected={selectedColumn?.uuid === column.uuid}
            onColumnChange={onColumnChange}
            onColumnRemoved={onColumnRemoved}
            onColumnSelected={onColumnSelected}
            onFocusNext={handleFocusNextColumn}
          />
        ))}
        {canAddColumn && isLastColumnFilled && (
          <List.Row onClick={() => onColumnSelected(null)} selected={selectedColumn === null}>
            <List.Cell width={300}>
              <TextInput
                ref={null === selectedColumn ? inputRef : null}
                onChange={onColumnCreated}
                placeholder={translate('akeneo.tailored_export.column_list.column_row.target_placeholder')}
                value=""
              />
            </List.Cell>
            <List.Cell width="auto">{translate('akeneo.tailored_export.column_list.column_row.no_source')}</List.Cell>
          </List.Row>
        )}
        {columnsConfiguration.length === 0 && <ColumnListPlaceholder onColumnCreated={onColumnCreated} />}
      </List>
    </Container>
  );
};

export {ColumnList};
