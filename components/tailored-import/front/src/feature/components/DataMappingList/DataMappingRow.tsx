import React from 'react';
import {Table} from 'akeneo-design-system';
import {Column, DataMapping, generateColumnName} from '../../models';
import {useTranslate} from '@akeneo-pim-community/shared';

type DataMappingRowProps = {
  dataMapping: DataMapping;
  columns: Column[];
};

const DataMappingRow = ({dataMapping, columns}: DataMappingRowProps) => {
  const translate = useTranslate();
  const sources = dataMapping.sources.map(uuid => {
    const column = columns.find(column => uuid === column.uuid);

    return column ? generateColumnName(column) : '';
  });

  return (
    <Table.Row>
      <Table.Cell>{dataMapping.target.code}</Table.Cell>
      <Table.Cell>
        {translate('akeneo.tailored_import.data_mapping.sources')}: {sources.join(' ')}
      </Table.Cell>
    </Table.Row>
  );
};

export {DataMappingRow};
