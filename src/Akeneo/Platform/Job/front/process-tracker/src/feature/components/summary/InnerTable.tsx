import React from 'react';
import styled from 'styled-components';
import {getColor, Table} from 'akeneo-design-system';

const TableContainer = styled(Table)`
  tr:last-child {
    td {
      border: none;
    }
  }

  td {
    padding: 10px;
  }

  background-color: ${getColor('white')};
`;

type InnerTableProps = {
  content: {[key: string]: string | object};
  className?: string;
};

const InnerTable = ({content, ...rest}: InnerTableProps) => {
  return (
    <TableContainer {...rest}>
      <Table.Body>
        {Object.entries(content).map(([key, value]) => (
          <Table.Row key={key}>
            <Table.Cell>{key}</Table.Cell>
            <Table.Cell>{'object' === typeof value ? JSON.stringify(value) : value}</Table.Cell>
          </Table.Row>
        ))}
      </Table.Body>
    </TableContainer>
  );
};

export {InnerTable};
