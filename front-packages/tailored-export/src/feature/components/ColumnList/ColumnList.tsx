import React, {ClipboardEvent, useEffect, useRef, useState} from 'react';
import {
  getColor,
  Helper,
  RulesIllustration,
  Search,
  SectionTitle,
  Table,
  TextInput,
  useAutoFocus,
  useBooleanState,
} from 'akeneo-design-system';
import styled from 'styled-components';
import {NoDataSection, NoDataTitle, useTranslate} from '@akeneo-pim-community/shared';
import {ColumnConfiguration, MAX_COLUMN_COUNT} from '../../models/ColumnConfiguration';
import {ColumnListPlaceholder} from './ColumnListPlaceholder';
import {ColumnRow, TargetCell} from './ColumnRow';
import {useValidationErrors} from '../../contexts';

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

const SpacedSearch = styled(Search)`
  margin: 20px 0;
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
  onFocusNext: () => void;
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
  onFocusNext,
}: ColumnListProps) => {
  const translate = useTranslate();
  const inputRef = useRef<HTMLInputElement>(null);
  const focus = useAutoFocus(inputRef);
  const [placeholderDisplayed, , hidePlaceholder] = useBooleanState(0 === columnsConfiguration.length);
  const [searchValue, setSearchValue] = useState<string>('');

  useEffect(() => {
    focus();
  }, [selectedColumn?.uuid, focus, placeholderDisplayed]);

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
  const filteredColumns = columnsConfiguration.filter(({target}) => target.includes(searchValue));

  const canAddColumn = MAX_COLUMN_COUNT > columnsConfiguration.length;
  const shouldDisplayNewColumnRow = canAddColumn && '' === searchValue;
  const shouldDisplayNoResults = !placeholderDisplayed && 0 === filteredColumns.length && '' !== searchValue;
  const shouldDisplayTable = !placeholderDisplayed && !shouldDisplayNoResults;

  return (
    <Container>
      <SectionTitle sticky={0}>
        <SectionTitle.Title>{translate('akeneo.tailored_export.column_list.title')}</SectionTitle.Title>
        <SectionTitle.Spacer />
      </SectionTitle>
      {!placeholderDisplayed && (
        <SpacedSearch
          sticky={44}
          placeholder={translate('pim_common.search')}
          searchValue={searchValue}
          onSearchChange={setSearchValue}
        >
          <Search.ResultCount>
            {translate('pim_common.result_count', {itemsCount: filteredColumns.length}, filteredColumns.length)}
          </Search.ResultCount>
        </SpacedSearch>
      )}
      {globalErrors.map((error, index) => (
        <Helper key={index} level="error">
          {translate(error.messageTemplate, error.parameters)}
        </Helper>
      ))}
      {placeholderDisplayed && <ColumnListPlaceholder onColumnCreated={hidePlaceholder} />}
      {shouldDisplayTable && (
        <Table isDragAndDroppable={true} onReorder={onColumnReorder}>
          <Table.Header sticky={88}>
            <Table.HeaderCell>{translate('akeneo.tailored_export.column_list.header.column_name')}</Table.HeaderCell>
            <SourceDataHeaderCell>
              {translate('akeneo.tailored_export.column_list.header.source_data')}
            </SourceDataHeaderCell>
            <Table.HeaderCell />
          </Table.Header>
          <Table.Body>
            {filteredColumns.map(column => (
              <ColumnRow
                key={column.uuid}
                ref={selectedColumn?.uuid === column.uuid ? inputRef : null}
                column={column}
                isSelected={selectedColumn?.uuid === column.uuid}
                onColumnChange={onColumnChange}
                onColumnRemoved={onColumnRemoved}
                onColumnSelected={onColumnSelected}
                onFocusNext={onFocusNext}
              />
            ))}
          </Table.Body>
          <Table.Body>
            {shouldDisplayNewColumnRow && (
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
      {shouldDisplayNoResults && (
        <NoDataSection>
          <RulesIllustration size={256} />
          <NoDataTitle>{translate('pim_common.no_search_result')}</NoDataTitle>
        </NoDataSection>
      )}
    </Container>
  );
};

export {ColumnList};
