import React from 'react';
import {TwoColumnsLayout} from './TwoColumnsLayout';
import {
  AddingValueIllustration,
  Button,
  CloseIcon,
  Helper,
  IconButton,
  Link,
  pimTheme,
  Placeholder,
  SectionTitle,
  Table,
  useBooleanState,
  uuid,
} from 'akeneo-design-system';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import styled, {ThemeProvider} from 'styled-components';
import {ColumnCode, ColumnDefinition, TableAttribute, TableConfiguration} from '../models';
import {getLabel, Locale, useFeatureFlags, useRouter, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {AddColumnModal} from './AddColumnModal';
import {DeleteColumnModal} from './DeleteColumnModal';
import {ColumnDefinitionProperties} from './ColumnDefinitionProperties';
import {LocaleRepository} from '../repositories';
import {LIMIT_OPTIONS} from './ManageOptionsModal';

const EmptyTableCell = styled(Table.Cell)`
  width: 44px;
`;

const AddNewColumnButton = styled(Button)`
  margin-top: 20px;
`;

const CenteredHelper = styled.div`
  text-align: center;
  padding: 0 20px;
`;

type TableStructureAppProps = {
  attribute: TableAttribute;
  initialTableConfiguration: TableConfiguration;
  onChange: (tableConfiguration: TableConfiguration) => void;
  savedColumnCodes: ColumnCode[];
  maxColumnCount?: number;
};

export type ColumnDefinitionWithId = ColumnDefinition & {id: string};
type TableConfigurationWithId = ColumnDefinitionWithId[];

const TableStructureApp: React.FC<TableStructureAppProps> = ({
  attribute,
  initialTableConfiguration,
  onChange,
  savedColumnCodes,
  maxColumnCount = 10,
}) => {
  const translate = useTranslate();
  const router = useRouter();
  const userContext = useUserContext();
  const featureFlags = useFeatureFlags();
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
    LocaleRepository.clearCache();
    LocaleRepository.findActivated(router).then((activeLocales: Locale[]) => setActiveLocales(activeLocales));
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
                isSelected={firstColumnDefinition.id === selectedColumnId}
              >
                <EmptyTableCell />
                <Table.Cell rowTitle={true}>
                  {getLabel(firstColumnDefinition.labels, userContext.get('catalogLocale'), firstColumnDefinition.code)}
                </Table.Cell>
              </Table.Row>
            </Table.Body>
          </Table>
          {tableConfiguration.length === 1 && (
            <Placeholder
              illustration={<AddingValueIllustration />}
              title={translate('pim_table_attribute.form.attribute.unique_title')}
            />
          )}
        </>
      ) : (
        <Placeholder
          illustration={<AddingValueIllustration />}
          title={translate('pim_table_attribute.form.attribute.empty_title')}
        />
      )}
      <Table isDragAndDroppable={true} onReorder={handleReorder}>
        <Table.Body>
          {otherColumnDefinitions.map(columnDefinition => (
            <Table.Row
              key={columnDefinition.id}
              onClick={() => setSelectedColumnId(columnDefinition.id)}
              isSelected={columnDefinition.id === selectedColumnId}
            >
              <Table.Cell rowTitle={true}>
                {getLabel(columnDefinition.labels, userContext.get('catalogLocale'), columnDefinition.code)}
              </Table.Cell>
              <Table.ActionCell>
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
              </Table.ActionCell>
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
          attributeLabel={getLabel(attribute.labels, userContext.get('catalogLocale'), attribute.code)}
        />
      )}
      {isNewColumnModalOpen && (
        <AddColumnModal
          close={closeNewColumnModal}
          onCreate={handleCreate}
          existingColumnCodes={tableConfiguration.map(columnDefinition => columnDefinition.code)}
        />
      )}
      {tableConfiguration.length < maxColumnCount && (
        <CenteredHelper>
          <AddNewColumnButton
            title={translate('pim_table_attribute.form.attribute.add_column')}
            ghost
            level='secondary'
            onClick={openNewColumnModal}
          >
            {translate('pim_table_attribute.form.attribute.add_column')}
          </AddNewColumnButton>
        </CenteredHelper>
      )}
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
        <Helper level='info'>
          {translate(
            featureFlags.isEnabled('reference_entity')
              ? 'pim_table_attribute.form.attribute.table_structure_helper_text_with_reference_entity'
              : 'pim_table_attribute.form.attribute.table_structure_helper_text',
            {limit: LIMIT_OPTIONS}
          )}{' '}
          <Link
            href='https://help.akeneo.com/pim/serenity/articles/manage-multidimensional-data-in-a-table.html'
            target='_blank'
          >
            {translate('pim_table_attribute.form.attribute.table_structure_helper_link')}
          </Link>
        </Helper>
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
