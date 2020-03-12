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

const SortableTableCell = styled.th`
  text-align: left;
  font-weight: normal;
  position: sticky;
  top: 177px;
  height: 43px;
  box-shadow: 0 1px 0 ${props => props.theme.color.grey120};
  background: ${props => props.theme.color.white};

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
      <thead>
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
      </thead>
      <tbody>
        {measurementFamilies.map(measurementFamily => (
          <MeasurementFamilyRow key={measurementFamily.code} measurementFamily={measurementFamily} />
        ))}
      </tbody>
    </Container>
  );
};

export {Table};
