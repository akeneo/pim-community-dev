import React from 'react';
import {TwoColumnsLayout} from './TwoColumnsLayout';
import {SectionTitle, pimTheme, Field, TextInput, Button, useBooleanState, Table} from 'akeneo-design-system';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {ColumnDefinition, TableConfiguration} from '../models/TableConfiguration';
import {getLabel, Locale} from '@akeneo-pim-community/shared';
import { AddColumnModal } from "./AddColumnModal";
const FetcherRegistry = require('pim/fetcher-registry');

type TableOptionsAppProps = {
  initialTableConfiguration: TableConfiguration;
  onChange: (tableConfiguration: TableConfiguration) => void;
};

const TableOptionsApp: React.FC<TableOptionsAppProps> = ({initialTableConfiguration, onChange}) => {
  const [tableConfiguration, setTableConfiguration] = React.useState<TableConfiguration>(initialTableConfiguration);
  const [selectedColumnCode, setSelectedColumnCode] = React.useState<string>(tableConfiguration[0].code);
  const selectedColumn = tableConfiguration.find(column => column.code === selectedColumnCode) as ColumnDefinition;
  const [activeLocales, setActiveLocales] = React.useState<Locale[]>([]);
  const [isNewColumnModalOpen, openNewColumnModal, closeNewColumnModal] = useBooleanState();

  React.useEffect(() => {
    FetcherRegistry.getFetcher('locale')
      .fetchActivated()
      .then((activeLocales: Locale[]) => setActiveLocales(activeLocales));
  }, []);

  const handleLabelChange = (localeCode: string, newValue: string) => {
    selectedColumn.labels[localeCode] = newValue;
    const index = tableConfiguration.indexOf(selectedColumn);
    tableConfiguration[index] = selectedColumn;
    setTableConfiguration([...tableConfiguration]);
    onChange(tableConfiguration);
  };

  const handleReorder = (newIndices) => {
    // TODO Backend does not work for this use case cause of integrity of order
    const newTableConfiguration = newIndices.map(i => tableConfiguration[i]);
    setTableConfiguration(newTableConfiguration);
    onChange(newTableConfiguration);
  }

  const handleCreate = (columnDefinition: ColumnDefinition) => {
    tableConfiguration.push(columnDefinition);
    setTableConfiguration([...tableConfiguration]);
    onChange(tableConfiguration);
  }

  const rightColumn = (
    <div>
      <SectionTitle title={getLabel(selectedColumn.labels, 'en_US', selectedColumn.code)}>
        {getLabel(selectedColumn.labels, 'en_US', selectedColumn.code)}
      </SectionTitle>
      <Field label="TODO code">
        <TextInput readOnly={true} value={selectedColumn.code} />
      </Field>
      <Field label="TODO data type">
        <TextInput readOnly={true} value={selectedColumn.data_type} />
      </Field>
      <SectionTitle title="TODO labels">TODO labels</SectionTitle>
      {activeLocales.map(locale => (
        <Field label={locale.label} key={locale.code}>
          <TextInput
            onChange={label => handleLabelChange(locale.code, label)}
            value={selectedColumn.labels[locale.code] ?? ''}
          />
        </Field>
      ))}
    </div>
  );
  return (
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <TwoColumnsLayout rightColumn={rightColumn}>
          <div>
            <SectionTitle title="TODO columns">TODO COLUMNS</SectionTitle>
            <Table>
              <Table.Body>
                {tableConfiguration.map(columnDefinition => (
                  <Table.Row
                    key={columnDefinition.code}
                    onClick={() => setSelectedColumnCode(columnDefinition.code)}
                    isSelected={columnDefinition.code === selectedColumnCode}
                  >
                    <Table.Cell>{getLabel(columnDefinition.labels, 'en_US', columnDefinition.code)}</Table.Cell>
                  </Table.Row>
                ))}
              </Table.Body>
            </Table>
            {isNewColumnModalOpen && <AddColumnModal close={closeNewColumnModal} onCreate={handleCreate} existingColumnCodes={tableConfiguration.map(columnDefinition => columnDefinition.code)}/>}
            <Button title={"TODO Add new column"} ghost level="secondary" onClick={openNewColumnModal}>TODO Add new column</Button>
          </div>
        </TwoColumnsLayout>
      </ThemeProvider>
    </DependenciesProvider>
  );
};

export {TableOptionsApp};
