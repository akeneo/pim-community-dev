import React from 'react';
import {Helper, SectionTitle, Table} from 'akeneo-design-system';
import styled from 'styled-components';
import {Column, DataMapping, MAX_DATA_MAPPING_COUNT} from '../../models';
import {getErrorsForPath, filterErrors, useTranslate, ValidationError} from '@akeneo-pim-community/shared';
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
  selectedDataMappingUuid: string | null;
  validationErrors: ValidationError[];
  onDataMappingAdded: (dataMapping: DataMapping) => void;
  onDataMappingSelected: (dataMappingUuid: string) => void;
};

const DataMappingList = ({
  dataMappings,
  columns,
  selectedDataMappingUuid,
  validationErrors,
  onDataMappingAdded,
  onDataMappingSelected,
}: DataMappingListProps) => {
  const translate = useTranslate();
  const canAddDataMapping = MAX_DATA_MAPPING_COUNT > dataMappings.length;
  const globalErrors = getErrorsForPath(validationErrors, '');

  return (
    <Container>
      <SectionTitle sticky={0}>
        <SectionTitle.Title>{translate('akeneo.tailored_import.data_mapping_list.title')}</SectionTitle.Title>
        <SectionTitle.Spacer />
        <AddDataMappingDropdown canAddDataMapping={canAddDataMapping} onDataMappingAdded={onDataMappingAdded} />
      </SectionTitle>
      {globalErrors.map((error, index) => (
        <Helper key={index} level="error">
          {translate(error.messageTemplate, error.parameters)}
        </Helper>
      ))}

      <Table>
        <Table.Body>
          {dataMappings.map(dataMapping => (
            <DataMappingRow
              key={dataMapping.uuid}
              dataMapping={dataMapping}
              columns={columns}
              onClick={onDataMappingSelected}
              isSelected={selectedDataMappingUuid === dataMapping.uuid}
              hasError={filterErrors(validationErrors, `[${dataMapping.uuid}]`).length > 0}
            />
          ))}
        </Table.Body>
      </Table>
    </Container>
  );
};

export type {DataMappingListProps};

export {DataMappingList};
