import React from 'react';
import {Search, Table, getColor} from 'akeneo-design-system';
import styled from 'styled-components';

const TimeToEnrichTable = () => {
  return (
    <>
      <Search onSearchChange={() => {}} placeholder="Search" searchValue="" title="Search">
        <StyledResults>34 results</StyledResults>
      </Search>

      <Table>
        <Table.Header>
          <Table.HeaderCell>Families</Table.HeaderCell>
          <Table.HeaderCell>Time-to-enrich (in days)</Table.HeaderCell>
          <Table.HeaderCell>Same period last year</Table.HeaderCell>
        </Table.Header>
        <Table.Body>
          <Table.Row>
            <Table.Cell>Global</Table.Cell>
            <Table.Cell>23</Table.Cell>
            <Table.Cell>21</Table.Cell>
          </Table.Row>
        </Table.Body>
      </Table>
    </>
  );
};

const StyledResults = styled('span')`
  color: ${getColor('brand', 100)};
`;

export {TimeToEnrichTable};
