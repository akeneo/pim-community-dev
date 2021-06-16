import React from 'react';
import {BooleanInput, TableInput} from 'akeneo-design-system';
import {ColumnCode, TableConfiguration} from '../models/TableConfiguration';
import {getLabel, useUserContext} from '@akeneo-pim-community/shared';
import {TableValue} from '../models/TableValue';

type TableInputValueProps = {
  valueData: TableValue;
  tableConfiguration: TableConfiguration;
  onChange: (tableValue: TableValue) => void;
};

const TableInputValue: React.FC<TableInputValueProps> = ({valueData, tableConfiguration, onChange}) => {
  const valueClone = valueData.map(row => {
    return Object.keys(row).reduce((previousRow, columnCode) => {
      previousRow[columnCode] = row[columnCode];
      return previousRow;
    }, {});
  });
  const [tableValue, setTableValue] = React.useState<TableValue>(valueClone);

  const handleChange = (rowIndex: number, columnCode: ColumnCode, cellValue: any) => {
    const row = tableValue[rowIndex];
    row[columnCode] = cellValue;
    tableValue[rowIndex] = row;
    const newTableValue = [...tableValue];
    setTableValue(newTableValue);
    onChange(newTableValue);
  };

  const userContext = useUserContext();

  return (
    <TableInput>
      <TableInput.Header>
        {tableConfiguration.map(columnDefinition => (
          <TableInput.HeaderCell key={columnDefinition.code}>
            {getLabel(columnDefinition.labels, userContext.get('catalogLocale'), columnDefinition.code)}
          </TableInput.HeaderCell>
        ))}
      </TableInput.Header>
      <TableInput.Body>
        {tableValue.map((row, rowIndex) => {
          return (
            <TableInput.Row key={`${rowIndex}`}>
              {tableConfiguration.map(columnDefinition => {
                const columnCode = columnDefinition.code;
                const columnType = columnDefinition.data_type;

                return (
                  <TableInput.Cell key={`${rowIndex}-${columnCode}`}>
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
  );
};

export {TableInputValue};
