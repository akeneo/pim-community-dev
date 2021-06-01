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
} from 'akeneo-design-system';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import styled, {ThemeProvider} from 'styled-components';
import {ColumnDefinition, TableConfiguration} from '../models/TableConfiguration';
import {getLabel, Locale, useRouter, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
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

type TableOptionsAppProps = {
  initialTableConfiguration: TableConfiguration;
  onChange: (tableConfiguration: TableConfiguration) => void;
};

const TableOptionsApp: React.FC<TableOptionsAppProps> = ({initialTableConfiguration, onChange}) => {
  const translate = useTranslate();
  const router = useRouter();
  const userContext = useUserContext();
  const [tableConfiguration, setTableConfiguration] = React.useState<TableConfiguration>(initialTableConfiguration);
  const [selectedColumnCode, setSelectedColumnCode] = React.useState<string>(tableConfiguration[0].code);
  const selectedColumn = tableConfiguration.find(column => column.code === selectedColumnCode) as ColumnDefinition;
  const [activeLocales, setActiveLocales] = React.useState<Locale[]>([]);
  const [isNewColumnModalOpen, openNewColumnModal, closeNewColumnModal] = useBooleanState();
  const [isDeleteColumnModalOpen, openDeleteColumnModal, closeDeleteColumnModal] = useBooleanState();
  const [lastColumnCodeToDelete, setLastColumnCodeToDelete] = React.useState<string | undefined>();
  const [firstColumnDefinition, ...otherColumnDefinitions] = tableConfiguration;

  React.useEffect(() => {
    fetchActivatedLocales(router).then((activeLocales: Locale[]) => setActiveLocales(activeLocales));
  }, [router]);

  const handleLabelChange = (localeCode: string, newValue: string) => {
    selectedColumn.labels[localeCode] = newValue;
    const index = tableConfiguration.indexOf(selectedColumn);
    tableConfiguration[index] = selectedColumn;
    setTableConfiguration([...tableConfiguration]);
    onChange(tableConfiguration);
  };

  const handleReorder = (newIndices: number[]) => {
    const newTableConfiguration = [firstColumnDefinition, ...newIndices.map(i => otherColumnDefinitions[i])];
    setTableConfiguration(newTableConfiguration);
    onChange(newTableConfiguration);
  };

  const handleCreate = (columnDefinition: ColumnDefinition) => {
    tableConfiguration.push(columnDefinition);
    setTableConfiguration([...tableConfiguration]);
    onChange(tableConfiguration);
  };

  const handleDelete = () => {
    const newTableConfiguration = tableConfiguration.filter(
      columnDefinition => columnDefinition.code !== lastColumnCodeToDelete
    );
    setTableConfiguration(newTableConfiguration);
    onChange(newTableConfiguration);
  };

  const rightColumn = (
    <div>
      <SectionTitle title={getLabel(selectedColumn.labels, userContext.get('catalogLocale'), selectedColumn.code)}>
        <SectionTitle.Title>
          {getLabel(selectedColumn.labels, userContext.get('catalogLocale'), selectedColumn.code)}
        </SectionTitle.Title>
      </SectionTitle>
      <FieldsList>
        <Field label={translate('pim_common.code')} requiredLabel={translate('pim_common.required_label')}>
          <TextInput readOnly={true} value={selectedColumn.code} />
        </Field>
        <Field
          label={translate('pim_table_attribute.form.attribute.data_type')}
          requiredLabel={translate('pim_common.required_label')}>
          <TextInput readOnly={true} value={selectedColumn.data_type} />
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
  );

  return (
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <TwoColumnsLayout rightColumn={rightColumn}>
          <div>
            <SectionTitle title={translate('pim_table_attribute.form.attribute.columns')}>
              <SectionTitle.Title>{translate('pim_table_attribute.form.attribute.columns')}</SectionTitle.Title>
            </SectionTitle>
            <Table>
              <Table.Body>
                <Table.Row
                  key={firstColumnDefinition.code}
                  onClick={() => setSelectedColumnCode(firstColumnDefinition.code)}
                  isSelected={firstColumnDefinition.code === selectedColumnCode}>
                  <EmptyTableCell />
                  <Table.Cell rowTitle={true}>
                    {getLabel(
                      firstColumnDefinition.labels,
                      userContext.get('catalogLocale'),
                      firstColumnDefinition.code
                    )}
                  </Table.Cell>
                </Table.Row>
              </Table.Body>
            </Table>
            <Table isDragAndDroppable={true} onReorder={handleReorder}>
              <Table.Body>
                {otherColumnDefinitions.map(columnDefinition => (
                  <Table.Row
                    key={columnDefinition.code}
                    onClick={() => setSelectedColumnCode(columnDefinition.code)}
                    isSelected={columnDefinition.code === selectedColumnCode}>
                    <Table.Cell rowTitle={true}>
                      {getLabel(columnDefinition.labels, userContext.get('catalogLocale'), columnDefinition.code)}
                    </Table.Cell>
                    {/* Adding the delete button */}
                    <Table.ActionCell>
                      <IconButton
                        ghost='borderless'
                        icon={<CloseIcon />}
                        onClick={() => {
                          setLastColumnCodeToDelete(columnDefinition.code);
                          openDeleteColumnModal();
                        }}
                        title='TODO:Delete'
                        level='tertiary'
                      />
                    </Table.ActionCell>
                  </Table.Row>
                ))}
              </Table.Body>
            </Table>
            {isDeleteColumnModalOpen && lastColumnCodeToDelete && (
              <DeleteColumnModal
                close={closeDeleteColumnModal}
                onDelete={handleDelete}
                columnCode={lastColumnCodeToDelete}
              />
            )}
            {isNewColumnModalOpen && (
              <AddColumnModal
                close={closeNewColumnModal}
                onCreate={handleCreate}
                existingColumnCodes={tableConfiguration.map(columnDefinition => columnDefinition.code)}
              />
            )}
            <AddNewColumnButton
              title={translate('pim_table_attribute.form.attribute.add_column')}
              ghost
              level='secondary'
              onClick={openNewColumnModal}>
              {translate('pim_table_attribute.form.attribute.add_column')}
            </AddNewColumnButton>
          </div>
        </TwoColumnsLayout>
      </ThemeProvider>
    </DependenciesProvider>
  );
};

export {TableOptionsApp};
