import React from 'react';
import { TableInput } from 'akeneo-design-system';
import { ColumnCode, TableConfiguration } from '../models/TableConfiguration';
import { getLabel, useUserContext } from '@akeneo-pim-community/shared';

type TableInputAppProps = {
  valueData: {[columnCode: string]: any}[];
  tableConfiguration: TableConfiguration;
};

const TableInputApp: React.FC<TableInputAppProps> = ({valueData, tableConfiguration}) => {

  const userContext = useUserContext();
  const columnCodes: ColumnCode[] = tableConfiguration.map(columnDefinition => columnDefinition.code);

  return <TableInput>
    <TableInput.Header>
      {tableConfiguration.map(columnDefinition => <TableInput.HeaderCell key={columnDefinition.code}>
        {getLabel(columnDefinition.labels, userContext.get('catalogLocale'), columnDefinition.code)}
      </TableInput.HeaderCell>)}
    </TableInput.Header>
    <TableInput.Body>
      {valueData.map((row, rowIndex) => {
        return (
          <TableInput.Row key={`${rowIndex}`}>
            {columnCodes.map(columnCode => <TableInput.Cell key={`${rowIndex}-${columnCode}`}>
              {JSON.stringify(row[columnCode])}
            </TableInput.Cell>)}
          </TableInput.Row>
        )
      })}
    </TableInput.Body>
  </TableInput>;
}

export {TableInputApp};
