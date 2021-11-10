import React from 'react';
import styled from 'styled-components';
import {routes} from './routes';
import {Table} from 'akeneo-design-system';

const ToolBarContainer = styled.div`
  position: fixed;
  bottom: 0;
  right: 0;
  background: white;
  padding: 10px;
  margin: 10px;
  box-shadow: 0 0 10px RGBa(0, 0, 0, 0.5)
`;

const ToolBar = ({route}: {route: string}) => {
  const data = routes[route];

  if (undefined === data) {
    return null;
  }

  return (
    <ToolBarContainer>
      <Table>
        <Table.Header>
          <Table.HeaderCell>Key</Table.HeaderCell>
          <Table.HeaderCell>Value</Table.HeaderCell>
        </Table.Header>
        <Table.Body>
          {Object.entries(data).map(([key, value]) => (
            <Table.Row key={key}>
              <Table.Cell>{key}</Table.Cell>
              <Table.Cell>{value}</Table.Cell>
            </Table.Row>
          ))}
        </Table.Body>
      </Table>
    </ToolBarContainer>
  )
}

export {ToolBar}
