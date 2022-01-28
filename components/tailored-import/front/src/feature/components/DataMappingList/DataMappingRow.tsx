import React from 'react';
import {Table} from 'akeneo-design-system';
import {Column, DataMapping, generateColumnName} from '../../models';
import {useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {Pill} from 'akeneo-design-system/lib/components/Pill/Pill';

type DataMappingRowProps = {
  dataMapping: DataMapping;
  columns: Column[];
  onClick: (dataMappingUuid: string) => void;
  hasError: boolean;
};

const Spacer = styled.div`
  flex: 1;
`;

const DataMappingRow = ({dataMapping, columns, onClick, hasError}: DataMappingRowProps) => {
  const translate = useTranslate();
  const sources = dataMapping.sources.map(uuid => {
    const column = columns.find(column => uuid === column.uuid);

    return column ? generateColumnName(column) : '';
  });

  return (
    <Table.Row onClick={() => onClick(dataMapping.uuid)}>
      <Table.Cell>{dataMapping.target.code}</Table.Cell>
      <Table.Cell>
        {sources.length === 0
          ? translate('akeneo.tailored_import.data_mapping_list.no_sources')
          : `${translate('akeneo.tailored_import.sources')}: ${sources.join(' ')}`}
        <Spacer />
        {hasError && <Pill level="danger" />}
      </Table.Cell>
    </Table.Row>
  );
};

export {DataMappingRow};
