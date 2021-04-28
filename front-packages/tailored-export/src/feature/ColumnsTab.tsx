import React, {SyntheticEvent, useEffect, useRef, useState} from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {getColor, getFontSize, Button, CloseIcon, IconButton, List, Link, SectionTitle, TextInput, useAutoFocus, AttributesIllustration, RulesIllustration} from 'akeneo-design-system';
import {ColumnsConfiguration, createColumn, addColumn, removeColumn, renameColumnTarget} from './models/ColumnConfiguration';

type ColumnProps = {
  columnsConfiguration: ColumnsConfiguration;
};

const Container = styled.div`
  padding-top: 10px;
  display: flex;
  gap: 20px;
  height: 100%;
  padding-bottom: 40px;
`;
const ColumnContainer = styled.div`
  flex: 1;
  height: 100%;
  overflow-y: auto;
`;
const SourceContainer = styled.div`
  height: 100%;
  overflow-y: auto;
  width: 400px;
`;
const ColumnPlaceHolder = styled.div`
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-top: 128px;
  padding: 20px;
  gap: 10px;
`;
const ColumnPlaceHolderTitle = styled.div`
  color: ${getColor('grey', 140)};
  font-size: ${getFontSize('title')};
`;
const ColumnPlaceHolderSubTitle = styled.div`
  color: ${getColor('grey', 100)};
  font-size: ${getFontSize('default')};
  text-align: center;
`;
const SourcePlaceHolder = styled.div`
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-top: 60px;
  padding: 20px;
  gap: 10px;
`;
const SourcePlaceHolderTitle = styled.div`
  color: ${getColor('grey', 140)};
  font-size: ${getFontSize('big')};
`;
const SourcePlaceHolderSubTitle = styled.div`
  color: ${getColor('grey', 100)};
  font-size: ${getFontSize('default')};
  text-align: center;
`;

const ColumnsTab = ({columnsConfiguration}: ColumnProps) => {
  const translate = useTranslate();
  const [columns, setColumns] = useState<ColumnsConfiguration>(columnsConfiguration);
  const inputRef = useRef<HTMLInputElement>(null);
  const focus = useAutoFocus(inputRef);
  const [selectedColumn, setSelectedColumn] = useState<string | null>(columnsConfiguration.length === 0 ? null : columnsConfiguration[0].uuid);
  const handleCreateColumn = (newColumnName: string) => {
    const column = createColumn(newColumnName);
    setColumns(columns => addColumn(columns, column));
    setSelectedColumn(column.uuid);
  };
  const handleFocusNextColumn = (columnUuid: string) => {
    const currentColumnIndex = columns.findIndex(({uuid}) => columnUuid === uuid);
    const nextColumn = columns[currentColumnIndex + 1];

    setSelectedColumn(undefined === nextColumn ? null : nextColumn.uuid);
  }
  const handleRemoveColumn = (event: SyntheticEvent, columnUuid: string) => {
    event.stopPropagation();
    setColumns(columns => removeColumn(columns, columnUuid));
    handleFocusNextColumn(columnUuid);
  }
  useEffect(() => {
    focus();
  }, [selectedColumn]);

  const selectedColumnConfiguration = columns.find(({uuid}) => selectedColumn === uuid);

  return (
    <Container>
      <ColumnContainer>
        <SectionTitle sticky={10}>
          <SectionTitle.Title>{translate('Columns')}</SectionTitle.Title>
          <SectionTitle.Spacer />
        </SectionTitle>
        <List>
          {columns.map(column => (
            <List.Row key={column.uuid} onClick={() => setSelectedColumn(column.uuid)}>
              <List.Cell width={300}>
                <TextInput
                  ref={column.uuid === selectedColumn ? inputRef : null}
                  onChange={updatedValue => {
                    setColumns(columns => renameColumnTarget(columns, column.uuid, updatedValue));
                  }}
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
                  onClick={(event) => handleRemoveColumn(event, column.uuid)}
                />
              </List.RemoveCell>
            </List.Row>
          ))}
          {columns.length > 0 && columns[columns.length - 1].target !== '' && (
            <List.Row onClick={() => setSelectedColumn(null)}>
              <List.Cell width={300}>
                <TextInput
                  ref={null === selectedColumn ? inputRef : null}
                  onChange={handleCreateColumn}
                  placeholder={translate('The column name')}
                  value=""
                />
              </List.Cell>
              <List.Cell width="auto">{translate('No source')}</List.Cell>
            </List.Row>
          )}
          {columns.length === 0 && (
            <ColumnPlaceHolder>
              <AttributesIllustration size={256} />
              <ColumnPlaceHolderTitle>{translate('No columns selection to export')}</ColumnPlaceHolderTitle>
              <ColumnPlaceHolderSubTitle>
                {translate('You must define your columns selection in order to export. If you don’t know how, ')}
                <Link href={'#'}>
                  {translate('take a look at this article.')}
                </Link>
              </ColumnPlaceHolderSubTitle>
              <Button
                  onClick={() => {
                    handleCreateColumn('');
                  }}
              >
                {translate('Add first column')}
              </Button>
            </ColumnPlaceHolder>
          )}
        </List>
      </ColumnContainer>
      <SourceContainer>
        <SectionTitle sticky={10}>
          <SectionTitle.Title>{translate('Source(s)')}</SectionTitle.Title>
          <SectionTitle.Spacer />
          <Button disabled={selectedColumn === null}>{translate('Add source')}</Button>
        </SectionTitle>
        {columns.length === 0 && (
          <SourcePlaceHolder>
            <RulesIllustration size={128} />
            <SourcePlaceHolderTitle>{translate('No column selected for the moment.')}</SourcePlaceHolderTitle>
          </SourcePlaceHolder>
        )}
        {(null === selectedColumn || (selectedColumnConfiguration && selectedColumnConfiguration.sources.length === 0)) && (
          <SourcePlaceHolder>
            <RulesIllustration size={128} />
            <SourcePlaceHolderTitle>{translate('No source selected for the moment.')}</SourcePlaceHolderTitle>
            <SourcePlaceHolderSubTitle>
              {translate('To know more about mappping and operations, ')}
              <Link href={'#'}>
                {translate('this article may help you')}
              </Link>
            </SourcePlaceHolderSubTitle>
          </SourcePlaceHolder>
        )}

        <div>{selectedColumn}</div>
      </SourceContainer>
    </Container>
  );
};

export {ColumnsTab};
export type {ColumnsConfiguration};
