import React from 'react';
import styled from 'styled-components';
import {SectionTitle} from 'akeneo-design-system';
import {useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {
  Column,
  DataMapping,
  ColumnIdentifier,
  FileStructure,
  filterColumnsByUuids,
  replaceSampleData,
  isAttributeDataMapping,
  isPropertyDataMapping,
  Target,
  isAttributeTarget,
  Operation,
  isPropertyTarget,
} from '../../models';
import {useSampleDataFetcher, useRefreshedSampleDataFetcher} from '../../hooks';
import {AttributeDataMappingDetails} from './AttributeDataMappingDetails';
import {PropertyDataMappingDetails} from './PropertyDataMappingDetails';

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

  const handleSourcesChange = async (sources: ColumnIdentifier[]) => {
    const columnIndices = filterColumnsByUuids(columns, sources).map(({index}) => index);
    const sampleData =
      0 < columnIndices.length
        ? await sampleDataFetcher(fileKey, columnIndices, fileStructure.sheet_name, fileStructure.first_product_row)
        : [];
    onDataMappingChange({...dataMapping, sources, sample_data: sampleData});
  };

  const handleTargetChange = (target: Target) => {
    if (isAttributeTarget(target)) {
      onDataMappingChange({...dataMapping, target});
    } else if (isPropertyTarget(target)) {
      onDataMappingChange({...dataMapping, target});
    }
  };

  const handleOperationsChange = (operations: Operation[]) => onDataMappingChange({...dataMapping, operations});

  const handleRefreshSampleData = async (indexToRefresh: number) => {
    const columnIndices = filterColumnsByUuids(columns, dataMapping.sources).map(({index}) => index);
    const refreshedData =
      0 < columnIndices.length
        ? await refreshedSampleDataFetcher(
            fileKey,
            dataMapping.sample_data,
            columnIndices,
            fileStructure.sheet_name,
            fileStructure.first_product_row
          )
        : null;

    onDataMappingChange({
      ...dataMapping,
      sample_data: replaceSampleData(dataMapping.sample_data, indexToRefresh, refreshedData),
    });
  };

  return (
    <DataMappingDetailsContainer>
      <SectionTitle sticky={0}>
        <SectionTitle.Title>{translate('akeneo.tailored_import.data_mapping.title')}</SectionTitle.Title>
      </SectionTitle>
      <Container>
        {isAttributeDataMapping(dataMapping) && (
          <AttributeDataMappingDetails
            columns={columns}
            dataMapping={dataMapping}
            validationErrors={validationErrors}
            onOperationsChange={handleOperationsChange}
            onRefreshSampleData={handleRefreshSampleData}
            onSourcesChange={handleSourcesChange}
            onTargetChange={handleTargetChange}
          />
        )}
        {isPropertyDataMapping(dataMapping) && (
          <PropertyDataMappingDetails
            columns={columns}
            dataMapping={dataMapping}
            validationErrors={validationErrors}
            onOperationsChange={handleOperationsChange}
            onRefreshSampleData={handleRefreshSampleData}
            onSourcesChange={handleSourcesChange}
            onTargetChange={handleTargetChange}
          />
        )}
      </Container>
    </DataMappingDetailsContainer>
  );
};

export {DataMappingDetails};
