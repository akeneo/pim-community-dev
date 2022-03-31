import React, {useState} from 'react';
import styled from 'styled-components';
import {SectionTitle} from 'akeneo-design-system';
import {filterErrors, useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {Column, DataMapping, Target, ColumnIdentifier, FileStructure, findColumnByUuid} from '../../models';
import {Sources} from './Sources';
import {TargetParameters} from './TargetParameters';
import {Operations} from './Operations';
import {useSampleDataFetcher, useRefreshedSampleDataFetcher} from '../../hooks';

const DataMappingDetailsContainer = styled.div`
  height: 100%;
  flex: 1;
  display: flex;
  flex-direction: column;
`;

const Container = styled.div`
  flex: 1;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 30px;
`;

type DataMappingDetailsProps = {
  fileKey: string;
  fileStructure: FileStructure;
  columns: Column[];
  dataMapping: DataMapping;
  validationErrors: ValidationError[];
  onDataMappingChange: (dataMapping: DataMapping) => void;
};

const DataMappingDetails = ({
  fileKey,
  fileStructure,
  columns,
  dataMapping,
  validationErrors,
  onDataMappingChange,
}: DataMappingDetailsProps) => {
  const translate = useTranslate();
  const sampleDataFetcher = useSampleDataFetcher();
  const refreshedSampleDataFetcher = useRefreshedSampleDataFetcher();
  const [loadingSampleData, setLoadingSampleData] = useState<number[]>([]);

  const handleSourcesChange = async (sources: ColumnIdentifier[]) => {
    const column = findColumnByUuid(columns, sources[0]);
    const sampleData =
      sources.length > 0 && null !== column
        ? await sampleDataFetcher(fileKey, column.index, fileStructure.sheet_name, fileStructure.first_product_row)
        : [];
    onDataMappingChange({...dataMapping, sources, sample_data: sampleData});
  };

  const handleTargetParametersChange = (target: Target) => {
    onDataMappingChange({...dataMapping, target});
  };

  const handleRefreshSampleData = async (index: number) => {
    setLoadingSampleData(loadingSampleData => [...loadingSampleData, index]);
    const column = findColumnByUuid(columns, dataMapping.sources[0]);
    const refreshedData =
      null !== column
        ? await refreshedSampleDataFetcher(
            fileKey,
            dataMapping.sample_data,
            column.index,
            fileStructure.sheet_name,
            fileStructure.first_product_row
          )
        : null;

    const sampleData = [...dataMapping.sample_data.slice(0, index), refreshedData, ...dataMapping.sample_data.slice(index + 1)]
    setLoadingSampleData([]);

    onDataMappingChange({...dataMapping, sample_data: sampleData});
  };

  return (
    <DataMappingDetailsContainer>
      <SectionTitle sticky={0}>
        <SectionTitle.Title>{translate('akeneo.tailored_import.data_mapping.title')}</SectionTitle.Title>
      </SectionTitle>
      <Container>
        <TargetParameters
          target={dataMapping.target}
          validationErrors={filterErrors(validationErrors, '[target]')}
          onTargetChange={handleTargetParametersChange}
        />
        <Sources
          sources={dataMapping.sources}
          columns={columns}
          validationErrors={filterErrors(validationErrors, '[sources]')}
          onSourcesChange={handleSourcesChange}
        />
        <Operations dataMapping={dataMapping} loadingSampleData={loadingSampleData} onRefreshSampleData={handleRefreshSampleData} />
      </Container>
    </DataMappingDetailsContainer>
  );
};

export {DataMappingDetails};
