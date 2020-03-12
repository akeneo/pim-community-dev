import React, {useContext} from 'react';
import styled from 'styled-components';
import {MeasurementFamilyRow} from 'akeneomeasure/pages/list/MeasurementFamilyRow';
import {MeasurementFamily} from 'akeneomeasure/model/measurement-family';
import {Caret, Direction} from 'akeneomeasure/shared/components/Caret';
import {TranslateContext} from 'akeneomeasure/context/translate-context';

const Container = styled.table`
  width: 100%;
  color: ${props => props.theme.color.grey140};
  border-collapse: collapse;

  td {
    width: 25%;
  }
`;

const TableHeader = styled.thead`
  tr {
    height: 43px;
    border-bottom: 1px solid ${props => props.theme.color.grey120};
  }
`;

const TableBody = styled.tbody``;

const SortableTableCell = styled.th`
  text-align: left;
  font-weight: normal;

  &:hover {
    cursor: pointer;
  }
`;

type TableProps = {
  measurementFamilies: MeasurementFamily[];
  toggleSortDirection: (columnCode: string) => void;
  getSortDirection: (columnCode: string) => Direction;
};

const Table = ({measurementFamilies, toggleSortDirection, getSortDirection}: TableProps) => {
  const __ = useContext(TranslateContext);

  return (
    <Container>
      <TableHeader>
        <tr>
          <SortableTableCell title={__('measurements.list.header.label')} onClick={() => toggleSortDirection('label')}>
            {__('measurements.list.header.label')}
            <Caret direction={getSortDirection('label')} />
          </SortableTableCell>
          <SortableTableCell title={__('measurements.list.header.code')} onClick={() => toggleSortDirection('code')}>
            {__('measurements.list.header.code')}
            <Caret direction={getSortDirection('code')} />
          </SortableTableCell>
          <SortableTableCell
            title={__('measurements.list.header.standard_unit')}
            onClick={() => toggleSortDirection('standard_unit')}
          >
            {__('measurements.list.header.standard_unit')}
            <Caret direction={getSortDirection('standard_unit')} />
          </SortableTableCell>
          <SortableTableCell
            title={__('measurements.list.header.unit_count')}
            onClick={() => toggleSortDirection('unit_count')}
          >
            {__('measurements.list.header.unit_count')}
            <Caret direction={getSortDirection('unit_count')} />
          </SortableTableCell>
        </tr>
      </TableHeader>
      <TableBody>
        {measurementFamilies.map(measurementFamily => (
          <MeasurementFamilyRow key={measurementFamily.code} measurementFamily={measurementFamily} />
        ))}
      </TableBody>
    </Container>
  );
};

export {Table};
