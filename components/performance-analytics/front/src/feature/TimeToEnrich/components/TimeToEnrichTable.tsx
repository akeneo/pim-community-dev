import React from 'react';
import {getColor, Table} from 'akeneo-design-system';
import {BigPill} from '../../Common/components/BigPill';
import styled from 'styled-components';
import {TimeToEnrich} from '../models';
import {useTranslate} from '@akeneo-pim-community/shared';

type Props = {
  tableData: TimeToEnrich[];
};

const EntityLabelCell = styled(Table.Cell)`
  color: ${getColor('purple', 100)};
`;

const TimeToEnrichTable = ({tableData}: Props) => {
  const translate = useTranslate();

  return (
    <Table>
      <Table.Header>
        <Table.HeaderCell>{translate('akeneo.performance_analytics.table.header_families')}</Table.HeaderCell>
        <Table.HeaderCell>
          <BigPill /> {translate('akeneo.performance_analytics.table.header_time_to_enrich')}
        </Table.HeaderCell>
      </Table.Header>
      <Table.Body>
        {tableData.map((row, index) => {
          return (
            <Table.Row key={index} onClick={() => {}}>
              <EntityLabelCell>{row.code}</EntityLabelCell>
              <Table.Cell>{row.value}</Table.Cell>
            </Table.Row>
          );
        })}
      </Table.Body>
    </Table>
  );
};

export {TimeToEnrichTable};
