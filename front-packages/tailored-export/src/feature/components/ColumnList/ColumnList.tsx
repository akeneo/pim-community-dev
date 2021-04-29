import React, {useEffect, useRef} from 'react';
import {CloseIcon, IconButton, List, SectionTitle, TextInput, useAutoFocus} from 'akeneo-design-system';
import {ColumnConfiguration, ColumnsConfiguration} from '../../models/ColumnConfiguration';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/shared';
import {SyntheticEvent} from 'react';
import {ColumnListPlaceholder} from './ColumnListPlaceholder';

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

  const handleColumnRemove = (event: SyntheticEvent, uuid: string) => {
    event.stopPropagation();
    onColumnRemoved(uuid);
    handleFocusNextColumn(uuid);
  };

  const handleFocusNextColumn = (columnUuid: string) => {
    const currentColumnIndex = columnsConfiguration.findIndex(({uuid}) => columnUuid === uuid);
    const nextColumn = columnsConfiguration[currentColumnIndex + 1];

    onColumnSelected(undefined === nextColumn ? null : nextColumn.uuid);
  };

  return (
    <Container>
      <SectionTitle sticky={0}>
        <SectionTitle.Title>{translate('Columns')}</SectionTitle.Title>
        <SectionTitle.Spacer />
      </SectionTitle>
      <List>
        {columnsConfiguration.map(column => (
          <List.Row key={column.uuid} onClick={() => onColumnSelected(column.uuid)}>
            <List.Cell width={300}>
              <TextInput
                ref={null !== selectedColumn && column.uuid === selectedColumn.uuid ? inputRef : null}
                onChange={updatedValue => onColumnChange({...column, target: updatedValue})}
                onSubmit={() => handleFocusNextColumn(column.uuid)}
                placeholder={translate('The column name')}
                value={column.target}
              />
            </List.Cell>
            <List.Cell width="auto">Sources list</List.Cell>
            <List.RemoveCell>
              <IconButton
                ghost="borderless"
                level="tertiary"
                icon={<CloseIcon />}
                title={translate('Remove column')}
                onClick={event => handleColumnRemove(event, column.uuid)}
              />
            </List.RemoveCell>
          </List.Row>
        ))}
        {columnsConfiguration.length > 0 && columnsConfiguration[columnsConfiguration.length - 1].target !== '' && (
          <List.Row onClick={() => onColumnSelected(null)}>
            <List.Cell width={300}>
              <TextInput
                ref={null === selectedColumn ? inputRef : null}
                onChange={onColumnCreated}
                placeholder={translate('The column name')}
                value=""
              />
            </List.Cell>
            <List.Cell width="auto">{translate('No source')}</List.Cell>
          </List.Row>
        )}
        {columnsConfiguration.length === 0 && <ColumnListPlaceholder onColumnCreated={onColumnCreated} />}
      </List>
    </Container>
  );
};

export {ColumnList};
