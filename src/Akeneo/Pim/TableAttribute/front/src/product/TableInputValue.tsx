import React from 'react';
import { AddingValueIllustration, BooleanInput, TableInput } from 'akeneo-design-system';
import {ColumnCode, TableConfiguration} from '../models/TableConfiguration';
import {getLabel, useUserContext} from '@akeneo-pim-community/shared';
import { TableFooter } from "./TableFooter";
import styled from "styled-components";
import { TableValueWithId } from "./TableFieldApp";

const TABLE_VALUE_ITEMS_PER_PAGE = [2, 5, 10, 20, 50, 100];

const CenteredHelper = styled.div`
  text-align: center;
`;

type TableInputValueProps = {
  valueData: TableValueWithId;
  tableConfiguration: TableConfiguration;
  onChange: (tableValue: TableValueWithId) => void;
  searchText: string;
};

const TableInputValue: React.FC<TableInputValueProps> = ({valueData, tableConfiguration, onChange, searchText = ''}) => {
  const handleChange = (rowIndex: number, columnCode: ColumnCode, cellValue: any) => {
    const row = valueData[rowIndex];
    row[columnCode] = cellValue;
    valueData[rowIndex] = row;
    const newTableValue = [...valueData];
    onChange(newTableValue);
  };

  const userContext = useUserContext();
  const [itemsPerPage, setItemsPerPage] = React.useState<number>(TABLE_VALUE_ITEMS_PER_PAGE[0]);
  const [currentPage, setCurrentPage] = React.useState<number>(0);
  const isSearching = searchText.trim() !== '';

  let filteredData = valueData;
  let valueDataPage = valueData.slice(currentPage * itemsPerPage, (currentPage + 1) * itemsPerPage);

  if (isSearching) {
    filteredData = valueData.filter(row => {
      return tableConfiguration.map(columnDefinition => columnDefinition.code).some(columnCode => {
        return (`${row[columnCode] || ''}`).indexOf(searchText) >= 0;
      });
    })
    valueDataPage = filteredData.slice(0, itemsPerPage);
  }

  const cellMatchSearch = (cellAsString: string) => {
    return isSearching && cellAsString.indexOf(searchText) >= 0;
  }

  if (isSearching) {
    valueDataPage = valueData.filter(row => {
      return tableConfiguration.map(columnDefinition => columnDefinition.code).some(columnCode => {
        return cellMatchSearch(`${row[columnCode]}`);
      });
    }).slice(0, itemsPerPage);
  }

  return (
    <div style={{marginBottom: 500, width: 1000}}>
      <TableInput>
        <TableInput.Header>
          {tableConfiguration.map(columnDefinition => (
            <TableInput.HeaderCell key={columnDefinition.code}>
              {getLabel(columnDefinition.labels, userContext.get('catalogLocale'), columnDefinition.code)}
            </TableInput.HeaderCell>
          ))}
        </TableInput.Header>
        <TableInput.Body>
          {valueDataPage.map((row, rowIndex) => {
            return (
              <TableInput.Row key={row["unique id"]}>
                {tableConfiguration.map(columnDefinition => {
                  const columnCode = columnDefinition.code;
                  const columnType = columnDefinition.data_type;

                  return (
                    <TableInput.Cell key={`${rowIndex}-${columnCode}`} highlighted={cellMatchSearch(`${row[columnCode]}`)}>
                      {'number' === columnType && (
                        <TableInput.NumberInput
                          value={`${row[columnCode] as number}`}
                          onChange={value => handleChange(rowIndex, columnCode, value)}
                        />
                      )}
                      {('text' === columnType || 'select' === columnType) && (
                        <TableInput.TextInput
                          value={row[columnCode] as string}
                          onChange={value => handleChange(rowIndex, columnCode, value)}
                        />
                      )}
                      {'boolean' === columnType && (
                        <BooleanInput
                          clearable={true}
                          clearLabel='Clear value'
                          noLabel='No'
                          yesLabel='Yes'
                          onChange={value => handleChange(rowIndex, columnCode, value)}
                          value={(row[columnCode] as boolean) ?? null}
                          readOnly={false}
                        />
                      )}
                    </TableInput.Cell>
                  );
                })}
              </TableInput.Row>
            );
          })}
        </TableInput.Body>
      </TableInput>
      {isSearching && valueDataPage.length === 0 &&
      <CenteredHelper>
        <AddingValueIllustration size={120} />
        No results!
      </CenteredHelper>
      }
      <TableFooter
        itemsPerPage={itemsPerPage}
        currentPage={currentPage}
        rowsCount={filteredData.length}
        setCurrentPage={setCurrentPage}
        setItemsPerPage={setItemsPerPage}
      />
    </div>
  );
};

export {TableInputValue, TABLE_VALUE_ITEMS_PER_PAGE};
