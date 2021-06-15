import React from 'react';
import { TableInput, TextInput } from 'akeneo-design-system';
import { ColumnCode, TableConfiguration } from '../models/TableConfiguration';
import { getLabel, useUserContext } from '@akeneo-pim-community/shared';

type TableValue = { [columnCode: string]: any }[];

type TableInputAppProps = {
  valueData: TableValue;
  tableConfiguration: TableConfiguration;
};

const TableInputApp: React.FC<TableInputAppProps> = ({valueData, tableConfiguration}) => {

  const [tableValue, setTableValue] = React.useState<TableValue>([...valueData]);

  const handleChange = (rowIndex: number, columnCode: ColumnCode, cellValue: string) => {
    const row = tableValue[rowIndex];
    delete row[columnCode];
    row[columnCode] = cellValue;
    tableValue[rowIndex] = {...row};
    setTableValue([...tableValue]);
  };

  const userContext = useUserContext();
  const columnCodes: ColumnCode[] = tableConfiguration.map(columnDefinition => columnDefinition.code);

  return <TableInput>
    <TableInput.Header>
      {tableConfiguration.map(columnDefinition => <TableInput.HeaderCell key={columnDefinition.code}>
        {getLabel(columnDefinition.labels, userContext.get('catalogLocale'), columnDefinition.code)}
      </TableInput.HeaderCell>)}
    </TableInput.Header>
    <TableInput.Body>
      {tableValue.map((row, rowIndex) => {
        return (
          <TableInput.Row key={`${rowIndex}`}>
            {columnCodes.map(columnCode => (
              <TableInput.Cell key={`${rowIndex}-${columnCode}`}>
                <TextInput value={row[columnCode]} onChange={(value) => handleChange(rowIndex, columnCode, value)} />
              </TableInput.Cell>))}
          </TableInput.Row>
        )
      })}
    </TableInput.Body>
  </TableInput>;
}

export {TableInputApp};
