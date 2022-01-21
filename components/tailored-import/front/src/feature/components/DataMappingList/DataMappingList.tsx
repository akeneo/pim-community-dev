import React from 'react';
import {Helper, SectionTitle, Table} from 'akeneo-design-system';
import styled from 'styled-components';
import {Column, DataMapping, MAX_DATA_MAPPING_COUNT} from '../../models';
import {useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {AddDataMappingDropdown} from '../AddDataMappingDropdown';
import {DataMappingRow} from './DataMappingRow';

const Container = styled.div`
  flex: 1;
  height: 100%;
  overflow-y: auto;
`;

type DataMappingListProps = {
  dataMappings: DataMapping[];
  columns: Column[];
  globalErrors: ValidationError[];
  onDataMappingCreated: (dataMapping: DataMapping) => void;
};

const DataMappingList = ({dataMappings, columns, globalErrors, onDataMappingCreated}: DataMappingListProps) => {
  const translate = useTranslate();
  const canAddDataMapping = MAX_DATA_MAPPING_COUNT > dataMappings.length;

  return (
    <Container>
      <SectionTitle sticky={0}>
        <SectionTitle.Title>{translate('akeneo.tailored_import.data_mapping.title')}</SectionTitle.Title>
        <SectionTitle.Spacer />
        <AddDataMappingDropdown canAddDataMapping={canAddDataMapping} onDataMappingAdded={onDataMappingCreated} />
      </SectionTitle>
      {globalErrors.map((error, index) => (
        <Helper key={index} level="error">
          {translate(error.messageTemplate, error.parameters)}
        </Helper>
      ))}

      <Table>
        <Table.Body>
          {dataMappings.map(dataMapping => (
            <DataMappingRow key={dataMapping.uuid} dataMapping={dataMapping} columns={columns} />
          ))}
        </Table.Body>
      </Table>
    </Container>
  );
};

export {DataMappingList};
