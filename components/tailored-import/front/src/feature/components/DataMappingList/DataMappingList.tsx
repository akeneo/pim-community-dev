import React from 'react';
import {Helper, SectionTitle, Table} from 'akeneo-design-system';
import styled from 'styled-components';
import {DataMapping} from '../../models';
import {useTranslate, ValidationError} from '@akeneo-pim-community/shared';

const Container = styled.div`
  flex: 1;
  height: 100%;
  overflow-y: auto;
`;

const ColumnNameHeaderCell = styled(Table.HeaderCell)`
  width: 300px;
`;

const SourceDataHeaderCell = styled(Table.HeaderCell)`
  padding-left: 20px;
`;

type DataMappingListProps = {
  dataMappings: DataMapping[];
  globalErrors: ValidationError[];
};

const DataMappingList = ({dataMappings, globalErrors}: DataMappingListProps) => {
  const translate = useTranslate();

  return (
    <Container>
      <SectionTitle sticky={0}>
        <SectionTitle.Title>{translate('akeneo.tailored_import.column_list.title')}</SectionTitle.Title>
        <SectionTitle.Spacer />
      </SectionTitle>
      {globalErrors.map((error, index) => (
        <Helper key={index} level="error">
          {translate(error.messageTemplate, error.parameters)}
        </Helper>
      ))}

      <Table>
        <Table.Header sticky={42}>
          <ColumnNameHeaderCell>
            {translate('akeneo.tailored_export.column_list.header.column_name')}
          </ColumnNameHeaderCell>
          <SourceDataHeaderCell>
            {translate('akeneo.tailored_export.column_list.header.source_data')}
          </SourceDataHeaderCell>
          <Table.HeaderCell />
        </Table.Header>
        <Table.Body>
          {dataMappings.map(dataMapping => (
            <>{dataMapping.target}</>
          ))}
        </Table.Body>
      </Table>
    </Container>
  );
};

export {DataMappingList};
