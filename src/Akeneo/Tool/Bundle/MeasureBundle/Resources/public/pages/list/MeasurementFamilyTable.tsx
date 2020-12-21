import React from 'react';
import styled from 'styled-components';
import {MeasurementFamilyRow} from 'akeneomeasure/pages/list/MeasurementFamilyRow';
import {MeasurementFamily} from 'akeneomeasure/model/measurement-family';
import {Direction} from 'akeneomeasure/model/direction';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {Table} from 'akeneo-design-system';

const SpacedTable = styled(Table)`
  th {
    padding-top: 15px;
  }
`;

type MeasurementFamilyTableProps = {
  measurementFamilies: MeasurementFamily[];
  toggleSortDirection: (columnCode: string) => void;
  getSortDirection: (columnCode: string) => Direction;
};

const MeasurementFamilyTable = ({
  measurementFamilies,
  toggleSortDirection,
  getSortDirection,
}: MeasurementFamilyTableProps) => {
  const translate = useTranslate();

  return (
    <SpacedTable>
      <Table.Header sticky={44}>
        <Table.HeaderCell
          isSortable={true}
          sortDirection={getSortDirection('label')}
          onDirectionChange={() => toggleSortDirection('label')}
        >
          {translate('pim_common.label')}
        </Table.HeaderCell>
        <Table.HeaderCell
          isSortable={true}
          sortDirection={getSortDirection('code')}
          onDirectionChange={() => toggleSortDirection('code')}
        >
          {translate('pim_common.code')}
        </Table.HeaderCell>
        <Table.HeaderCell
          isSortable={true}
          sortDirection={getSortDirection('standard_unit')}
          onDirectionChange={() => toggleSortDirection('standard_unit')}
        >
          {translate('measurements.list.header.standard_unit')}
        </Table.HeaderCell>
        <Table.HeaderCell
          isSortable={true}
          sortDirection={getSortDirection('unit_count')}
          onDirectionChange={() => toggleSortDirection('unit_count')}
        >
          {translate('measurements.list.header.unit_count')}
        </Table.HeaderCell>
      </Table.Header>
      <Table.Body>
        {measurementFamilies.map(measurementFamily => (
          <MeasurementFamilyRow key={measurementFamily.code} measurementFamily={measurementFamily} />
        ))}
      </Table.Body>
    </SpacedTable>
  );
};

export {MeasurementFamilyTable};
