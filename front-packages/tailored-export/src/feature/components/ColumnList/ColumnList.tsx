import React, {ClipboardEvent, useEffect, useRef} from 'react';
import {getColor, Helper, SectionTitle, Table, TextInput, useAutoFocus, useBooleanState} from 'akeneo-design-system';
import {ColumnConfiguration} from '../../models/ColumnConfiguration';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/shared';
import {ColumnListPlaceholder} from './ColumnListPlaceholder';
import {ColumnRow, TargetCell} from './ColumnRow';
import {useValidationErrors} from '../../contexts';
import {MAX_COLUMN_COUNT} from '../../ColumnsTab';

const Container = styled.div`
  flex: 1;
  height: 100%;
  overflow-y: auto;
`;

const SourceList = styled.div`
  color: ${getColor('grey', 100)};
  font-style: italic;
  margin-left: 20px;
`;

const SourceDataHeaderCell = styled(Table.HeaderCell)`
  padding-left: 20px;
`;

type ColumnListProps = {
  columnsConfiguration: ColumnConfiguration[];
  selectedColumn: ColumnConfiguration | null;
  onColumnCreated: (target: string) => void;
  onColumnsCreated: (targets: string[]) => void;
  onColumnChange: (column: ColumnConfiguration) => void;
  onColumnSelected: (uuid: string | null) => void;
  onColumnRemoved: (uuid: string) => void;
  onColumnReorder: (newIndices: number[]) => void;
};

const ColumnList = ({
  columnsConfiguration,
  selectedColumn,
  onColumnCreated,
  onColumnsCreated,
  onColumnChange,
  onColumnSelected,
  onColumnRemoved,
  onColumnReorder,
}: ColumnListProps) => {
  const translate = useTranslate();
  const inputRef = useRef<HTMLInputElement>(null);
  const focus = useAutoFocus(inputRef);
  const [placeholderDisplayed, , hidePlaceholder] = useBooleanState(0 === columnsConfiguration.length);

  useEffect(() => {
    focus();
  }, [selectedColumn, focus, placeholderDisplayed]);

  const handleFocusNextColumn = (columnUuid: string) => {
    const currentColumnIndex = columnsConfiguration.findIndex(({uuid}) => columnUuid === uuid);
    const nextColumn = columnsConfiguration[currentColumnIndex + 1];

    onColumnSelected(undefined === nextColumn ? null : nextColumn.uuid);
  };

  const handlePaste = (event: ClipboardEvent<HTMLInputElement>) => {
    const clipboardData = event.clipboardData;
    const pastedData = clipboardData?.getData('Text');

    const pastedColumns = pastedData?.split('\t');

    const currentColumnIsEmpty = null === selectedColumn || '' === selectedColumn.target;
    const currentColumnIsLastColumn =
      null === selectedColumn || columnsConfiguration.indexOf(selectedColumn) === columnsConfiguration.length - 1;
    if (undefined !== pastedColumns && pastedColumns.length > 1 && currentColumnIsEmpty && currentColumnIsLastColumn) {
      event.preventDefault(); // We need to prevent default to not trigger onChange event
      onColumnsCreated(pastedColumns.filter(Boolean));
    }
  };

  const globalErrors = useValidationErrors('[columns]', true);

  const canAddColumn = MAX_COLUMN_COUNT > columnsConfiguration.length;

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
      {!placeholderDisplayed && (
        <Table isDragAndDroppable={true} onReorder={onColumnReorder}>
          <Table.Header sticky={44}>
            <Table.HeaderCell>{translate('akeneo.tailored_export.column_list.header.column_name')}</Table.HeaderCell>
            <SourceDataHeaderCell>
              {translate('akeneo.tailored_export.column_list.header.source_data')}
            </SourceDataHeaderCell>
            <Table.HeaderCell />
          </Table.Header>
          <Table.Body>
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
          </Table.Body>
          <Table.Body>
            {canAddColumn && (
              <Table.Row onClick={() => onColumnSelected(null)} isSelected={selectedColumn === null}>
                <TargetCell>
                  <TextInput
                    ref={null === selectedColumn ? inputRef : null}
                    onChange={onColumnCreated}
                    onPaste={handlePaste}
                    placeholder={translate('akeneo.tailored_export.column_list.column_row.target_placeholder')}
                    value=""
                  />
                </TargetCell>
                <Table.Cell>
                  <SourceList>{translate('akeneo.tailored_export.column_list.column_row.no_source')}</SourceList>
                </Table.Cell>
                <Table.Cell />
              </Table.Row>
            )}
          </Table.Body>
        </Table>
      )}
      {placeholderDisplayed && <ColumnListPlaceholder onColumnCreated={hidePlaceholder} />}
    </Container>
  );
};

export {ColumnList};
