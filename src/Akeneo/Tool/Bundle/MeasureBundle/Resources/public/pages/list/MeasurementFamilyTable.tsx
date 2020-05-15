import React from 'react';
import styled from 'styled-components';
import {MeasurementFamilyRow} from 'akeneomeasure/pages/list/MeasurementFamilyRow';
import {MeasurementFamily} from 'akeneomeasure/model/measurement-family';
import {Caret} from 'akeneomeasure/shared/components/Caret';
import {Table, HeaderCell} from 'akeneomeasure/pages/common/Table';
import {Direction} from 'akeneomeasure/model/direction';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

const SortableHeaderCell = styled(HeaderCell)`
  &:hover {
    cursor: pointer;
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
  const __ = useTranslate();

  return (
    <Table>
      <thead>
        <tr>
          <SortableHeaderCell title={__('pim_common.label')} onClick={() => toggleSortDirection('label')}>
            {__('pim_common.label')}
            <Caret direction={getSortDirection('label')} />
          </SortableHeaderCell>
          <SortableHeaderCell title={__('pim_common.code')} onClick={() => toggleSortDirection('code')}>
            {__('pim_common.code')}
            <Caret direction={getSortDirection('code')} />
          </SortableHeaderCell>
          <SortableHeaderCell
            title={__('measurements.list.header.standard_unit')}
            onClick={() => toggleSortDirection('standard_unit')}
          >
            {__('measurements.list.header.standard_unit')}
            <Caret direction={getSortDirection('standard_unit')} />
          </SortableHeaderCell>
          <SortableHeaderCell
            title={__('measurements.list.header.unit_count')}
            onClick={() => toggleSortDirection('unit_count')}
          >
            {__('measurements.list.header.unit_count')}
            <Caret direction={getSortDirection('unit_count')} />
          </SortableHeaderCell>
        </tr>
      </thead>
      <tbody>
        {measurementFamilies.map(measurementFamily => (
          <MeasurementFamilyRow key={measurementFamily.code} measurementFamily={measurementFamily} />
        ))}
      </tbody>
    </Table>
  );
};

export {MeasurementFamilyTable};
