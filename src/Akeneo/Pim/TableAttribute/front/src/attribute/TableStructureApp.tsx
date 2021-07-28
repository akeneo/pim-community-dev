import React from 'react';
import {TwoColumnsLayout} from './TwoColumnsLayout';
import {
  AddingValueIllustration,
  AkeneoThemedProps,
  Button,
  CloseIcon,
  getColor,
  getFontSize,
  IconButton,
  pimTheme,
  SectionTitle,
  Table,
  useBooleanState,
  uuid,
} from 'akeneo-design-system';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import styled, {ThemeProvider} from 'styled-components';
import {ColumnCode, ColumnDefinition, TableConfiguration} from '../models/TableConfiguration';
import {getLabel, Locale, useRouter, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {AddColumnModal} from './AddColumnModal';
import {DeleteColumnModal} from './DeleteColumnModal';
import {ColumnDefinitionProperties} from './ColumnDefinitionProperties';
import {Attribute} from '../models/Attribute';
import {getActivatedLocales} from '../repositories/Locale';

const EmptyTableCell = styled(Table.Cell)`
  width: 44px;
`;

const AddNewColumnButton = styled(Button)`
  margin-top: 20px;
`;

const CenteredHelper = styled.div<{centered: boolean} & AkeneoThemedProps>`
  text-align: ${({centered}) => (centered ? 'center' : 'initial')};
`;

const EmptyTableTitle = styled.div`
  font-size: ${getFontSize('big')};
  color: ${getColor('grey', 140)};
`;

type TableStructureAppProps = {
  attribute: Attribute;
  initialTableConfiguration: TableConfiguration;
  onChange: (tableConfiguration: TableConfiguration) => void;
  savedColumnCodes: ColumnCode[];
};

export type ColumnDefinitionWithId = ColumnDefinition & {id: string};
type TableConfigurationWithId = ColumnDefinitionWithId[];

const TableStructureActionCell = styled(Table.ActionCell)`
  width: 44px;
`;

const TableStructureApp: React.FC<TableStructureAppProps> = ({
  attribute,
  initialTableConfiguration,
  onChange,
  savedColumnCodes,
}) => {
  const translate = useTranslate();
  const router = useRouter();
  const userContext = useUserContext();
  const [tableConfiguration, setTableConfiguration] = React.useState<TableConfigurationWithId>(
    initialTableConfiguration.map(columnDefinition => {
      return {...columnDefinition, id: uuid()};
    })
  );
  const [selectedColumnId, setSelectedColumnId] = React.useState<string | undefined>(tableConfiguration[0]?.id);
  const selectedColumn = tableConfiguration.find(column => column.id === selectedColumnId) as ColumnDefinitionWithId;
  const [activeLocales, setActiveLocales] = React.useState<Locale[]>([]);
  const [isNewColumnModalOpen, openNewColumnModal, closeNewColumnModal] = useBooleanState();
  const [isDeleteColumnModalOpen, openDeleteColumnModal, closeDeleteColumnModal] = useBooleanState();
  const [lastColumnIdToDelete, setLastColumnIdToDelete] = React.useState<string | undefined>();
  const [firstColumnDefinition, ...otherColumnDefinitions] = tableConfiguration;
  const [savedColumnIds, setSavedColumnIds] = React.useState<string[]>([]);

  React.useEffect(() => {
    getActivatedLocales(router).then((activeLocales: Locale[]) => setActiveLocales(activeLocales));
  }, [router]);

  React.useEffect(() => {
    setSavedColumnIds(
      savedColumnCodes.map(
        savedColumnCode =>
          tableConfiguration.find(columnDefinition => columnDefinition.code === savedColumnCode)?.id as string
      )
    );
  }, [JSON.stringify(savedColumnCodes)]);

  const isDuplicateColumnCode = (columnCode: ColumnCode) => {
    return tableConfiguration.filter(columnDefinition => columnDefinition.code === columnCode).length > 1;
  };

  const handleChange = (tableConfigurationWithId: TableConfigurationWithId) => {
    onChange(
      tableConfigurationWithId.map(columnDefinition => {
        // eslint-disable-next-line @typescript-eslint/no-unused-vars
        const {id, ...rest} = columnDefinition;
        return rest;
      })
    );
  };

  const handleColumnChange = (columnDefinition: ColumnDefinitionWithId) => {
    const index = tableConfiguration.indexOf(selectedColumn);
    tableConfiguration[index] = columnDefinition;
    setTableConfiguration([...tableConfiguration]);
    handleChange(tableConfiguration);
  };

  const handleReorder = (newIndices: number[]) => {
    const newTableConfiguration = [firstColumnDefinition, ...newIndices.map(i => otherColumnDefinitions[i])];
    setTableConfiguration(newTableConfiguration);
    handleChange(newTableConfiguration);
  };

  const handleCreate = (columnDefinition: ColumnDefinition) => {
    const columnDefinitionWithId = {...columnDefinition, id: uuid()};
    tableConfiguration.push(columnDefinitionWithId);
    setTableConfiguration([...tableConfiguration]);
    setSelectedColumnId(columnDefinitionWithId.id);
    handleChange(tableConfiguration);
  };

  const handleDelete = () => {
    const newTableConfiguration = tableConfiguration.filter(
      columnDefinition => columnDefinition.id !== lastColumnIdToDelete
    );
    setTableConfiguration(newTableConfiguration);
    if (lastColumnIdToDelete === selectedColumnId) {
      setSelectedColumnId(newTableConfiguration[0].id);
    }
    handleChange(newTableConfiguration);
  };

  const leftColumn = (
    <div>
      <SectionTitle title={translate('pim_table_attribute.form.attribute.column_management')}>
        <SectionTitle.Title>{translate('pim_table_attribute.form.attribute.column_management')}</SectionTitle.Title>
      </SectionTitle>
      {tableConfiguration.length > 0 ? (
        <>
          <Table>
            <Table.Body>
              <Table.Row
                onClick={() => setSelectedColumnId(firstColumnDefinition.id)}
                isSelected={firstColumnDefinition.id === selectedColumnId}>
                <EmptyTableCell />
                <Table.Cell rowTitle={true}>
                  {getLabel(firstColumnDefinition.labels, userContext.get('catalogLocale'), firstColumnDefinition.code)}
                </Table.Cell>
              </Table.Row>
            </Table.Body>
          </Table>
          {tableConfiguration.length === 1 && (
            <CenteredHelper centered={true}>
              <AddingValueIllustration size={120} />
              <EmptyTableTitle>{translate('pim_table_attribute.form.attribute.unique_title')}</EmptyTableTitle>
              {translate('pim_table_attribute.form.attribute.unique_subtitle')}
            </CenteredHelper>
          )}
        </>
      ) : (
        <CenteredHelper centered={true}>
          <AddingValueIllustration size={120} />
          <EmptyTableTitle>{translate('pim_table_attribute.form.attribute.empty_title')}</EmptyTableTitle>
          {translate('pim_table_attribute.form.attribute.empty_subtitle')}
        </CenteredHelper>
      )}
      <Table isDragAndDroppable={true} onReorder={handleReorder}>
        <Table.Body>
          {otherColumnDefinitions.map(columnDefinition => (
            <Table.Row
              key={columnDefinition.id}
              onClick={() => setSelectedColumnId(columnDefinition.id)}
              isSelected={columnDefinition.id === selectedColumnId}>
              <Table.Cell rowTitle={true}>
                {getLabel(columnDefinition.labels, userContext.get('catalogLocale'), columnDefinition.code)}
              </Table.Cell>
              <TableStructureActionCell>
                <IconButton
                  ghost='borderless'
                  icon={<CloseIcon />}
                  onClick={() => {
                    setLastColumnIdToDelete(columnDefinition.id);
                    openDeleteColumnModal();
                  }}
                  title={translate('pim_common.delete')}
                  level='tertiary'
                />
              </TableStructureActionCell>
            </Table.Row>
          ))}
        </Table.Body>
      </Table>
      {isDeleteColumnModalOpen && lastColumnIdToDelete && (
        <DeleteColumnModal
          close={closeDeleteColumnModal}
          onDelete={handleDelete}
          columnDefinitionCode={
            tableConfiguration.find(columnDefinition => columnDefinition.id === lastColumnIdToDelete)
              ?.code as ColumnCode
          }
        />
      )}
      {isNewColumnModalOpen && (
        <AddColumnModal
          close={closeNewColumnModal}
          onCreate={handleCreate}
          existingColumnCodes={tableConfiguration.map(columnDefinition => columnDefinition.code)}
        />
      )}
      <CenteredHelper centered={tableConfiguration.length === 0}>
        <AddNewColumnButton
          title={translate('pim_table_attribute.form.attribute.add_column')}
          ghost
          level='secondary'
          onClick={openNewColumnModal}>
          {translate('pim_table_attribute.form.attribute.add_column')}
        </AddNewColumnButton>
      </CenteredHelper>
    </div>
  );

  const ColumnDefinitionColumn = selectedColumn ? (
    <ColumnDefinitionProperties
      attribute={attribute}
      selectedColumn={selectedColumn}
      catalogLocaleCode={userContext.get('catalogLocale')}
      activeLocales={activeLocales}
      onChange={handleColumnChange}
      savedColumnIds={savedColumnIds}
      isDuplicateColumnCode={isDuplicateColumnCode}
    />
  ) : (
    <div />
  );

  return (
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        {tableConfiguration.length > 0 ? (
          <TwoColumnsLayout rightColumn={ColumnDefinitionColumn}>{leftColumn}</TwoColumnsLayout>
        ) : (
          leftColumn
        )}
      </ThemeProvider>
    </DependenciesProvider>
  );
};

export {TableStructureApp};
