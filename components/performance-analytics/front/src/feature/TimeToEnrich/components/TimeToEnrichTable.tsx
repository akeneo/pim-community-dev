import React, {useState, useEffect} from 'react';
import {getColor, Table} from 'akeneo-design-system';
import {BigPill} from '../../Common/components/BigPill';
import styled from 'styled-components';
import {TimeToEnrich, TimeToEnrichEntityType} from '../models';
import {useTranslate, userContext} from '@akeneo-pim-community/shared';
import {Family, getFamilyLabel, useFetchers, getAllFamilies} from '../../Common';

type Props = {
  tableData: TimeToEnrich[];
  entityType?: TimeToEnrichEntityType;
};

const EntityLabelCell = styled(Table.Cell)`
  color: ${getColor('purple', 100)};
`;

const TimeToEnrichTable = ({tableData, entityType = 'family'}: Props) => {
  const translate = useTranslate();
  userContext.get('catalogLocale');
  const fetcher = useFetchers();
  const [families, setFamilies] = useState<{[familyCode: string]: Family} | undefined>(undefined);

  useEffect(() => {
    getAllFamilies(fetcher).then(families => setFamilies(families));
  }, [fetcher]);

  const getLabel = (code: string) => {
    if ('family' === entityType && typeof families !== 'undefined' && typeof families[code] !== 'undefined') {
      return getFamilyLabel(families[code], userContext.get('catalogLocale'));
    }

    return `[${code}]`;
  };

  if (typeof families === 'undefined') {
    return <></>;
  }

  return (
    <Table>
      <Table.Header>
        <Table.HeaderCell>{translate('akeneo.performance_analytics.table.header_' + entityType)}</Table.HeaderCell>
        <Table.HeaderCell>
          <BigPill /> {translate('akeneo.performance_analytics.table.header_time_to_enrich')}
        </Table.HeaderCell>
      </Table.Header>
      <Table.Body>
        {tableData.map((row, index) => {
          return (
            <Table.Row key={index} onClick={() => {}}>
              <EntityLabelCell>{getLabel(row.code)}</EntityLabelCell>
              <Table.Cell>{row.value}</Table.Cell>
            </Table.Row>
          );
        })}
      </Table.Body>
    </Table>
  );
};

export {TimeToEnrichTable};
