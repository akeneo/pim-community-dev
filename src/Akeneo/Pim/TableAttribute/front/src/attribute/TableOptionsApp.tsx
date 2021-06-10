import React from 'react';
import {TwoColumnsLayout} from './TwoColumnsLayout';
import {
  SectionTitle,
  pimTheme,
  Field,
  TextInput,
  Button,
  useBooleanState,
  Table,
  CloseIcon,
  IconButton,
  AddingValueIllustration,
  AkeneoThemedProps,
  getFontSize,
  getColor,
  uuid,
  Helper,
} from 'akeneo-design-system';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import styled, {ThemeProvider} from 'styled-components';
import {ColumnCode, ColumnDefinition, TableConfiguration} from '../models/TableConfiguration';
import {getLabel, Locale, LocaleCode, useRouter, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {AddColumnModal} from './AddColumnModal';
import {DeleteColumnModal} from './DeleteColumnModal';
import {fetchActivatedLocales} from '../fetchers/LocaleFetcher';

const FieldsList = styled.div`
  display: flex;
  flex-direction: column;
  gap: 20px;
  margin: 20px 0;
`;

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

type TableOptionsAppProps = {
  initialTableConfiguration: TableConfiguration;
  onChange: (tableConfiguration: TableConfiguration) => void;
  savedColumnCodes: ColumnCode[];
};

type ColumnDefinitionWithId = ColumnDefinition & {id: string};
type TableConfigurationWithId = ColumnDefinitionWithId[];

const TableOptionsActionCell = styled(Table.ActionCell)`
  width: 44px;
`;

const TableOptionsApp: React.FC<TableOptionsAppProps> = ({initialTableConfiguration, onChange, savedColumnCodes}) => {
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
    fetchActivatedLocales(router).then((activeLocales: Locale[]) => setActiveLocales(activeLocales));
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
        const {id, ...rest} = columnDefinition;
        return rest;
      })
    );
  };

  const handleLabelChange = (localeCode: LocaleCode, newValue: string) => {
    selectedColumn.labels[localeCode] = newValue;
    const index = tableConfiguration.indexOf(selectedColumn);
    tableConfiguration[index] = selectedColumn;
    setTableConfiguration([...tableConfiguration]);
    handleChange(tableConfiguration);
  };

  const handleCodeChange = (newCode: ColumnCode) => {
    selectedColumn.code = newCode;
    const index = tableConfiguration.indexOf(selectedColumn);
    tableConfiguration[index] = selectedColumn;
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
      <SectionTitle title={translate('pim_table_attribute.form.attribute.columns')}>
        <SectionTitle.Title>{translate('pim_table_attribute.form.attribute.columns')}</SectionTitle.Title>
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
              <TableOptionsActionCell>
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
              </TableOptionsActionCell>
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

  const rightColumn = selectedColumn ? (
    <div>
      <SectionTitle title={getLabel(selectedColumn.labels, userContext.get('catalogLocale'), selectedColumn.code)}>
        <SectionTitle.Title>
          {getLabel(selectedColumn.labels, userContext.get('catalogLocale'), selectedColumn.code)}
        </SectionTitle.Title>
      </SectionTitle>
      <FieldsList>
        <Field label={translate('pim_common.code')} requiredLabel={translate('pim_common.required_label')}>
          <TextInput
            readOnly={savedColumnIds.includes(selectedColumn.id)}
            value={selectedColumn.code}
            onChange={handleCodeChange}
          />
          {isDuplicateColumnCode(selectedColumn.code) && (
            <Helper level='error'>
              {translate('pim_table_attribute.validations.duplicated_column_code', {
                duplicateCode: selectedColumn.code,
              })}
            </Helper>
          )}
        </Field>
        <Field
          label={translate('pim_table_attribute.form.attribute.data_type')}
          requiredLabel={translate('pim_common.required_label')}>
          <TextInput
            readOnly={true}
            value={translate(`pim_table_attribute.properties.data_type.${selectedColumn.data_type}`)}
          />
        </Field>
      </FieldsList>
      <SectionTitle title={translate('pim_table_attribute.form.attribute.labels')}>
        <SectionTitle.Title>{translate('pim_table_attribute.form.attribute.labels')}</SectionTitle.Title>
      </SectionTitle>
      <FieldsList>
        {activeLocales.map(locale => (
          <Field label={locale.label} key={locale.code} locale={locale.code}>
            <TextInput
              onChange={label => handleLabelChange(locale.code, label)}
              value={selectedColumn.labels[locale.code] ?? ''}
            />
          </Field>
        ))}
      </FieldsList>
    </div>
  ) : (
    <div />
  );

  return (
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        {tableConfiguration.length > 0 ? (
          <TwoColumnsLayout rightColumn={rightColumn}>{leftColumn}</TwoColumnsLayout>
        ) : (
          leftColumn
        )}
      </ThemeProvider>
    </DependenciesProvider>
  );
};

export {TableOptionsApp};
