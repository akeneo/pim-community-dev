import React from 'react';
import styled from 'styled-components';
import {SectionTitle} from 'akeneo-design-system';
import {useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {
  Column,
  DataMapping,
  ColumnIdentifier,
  FileStructure,
  findColumnByUuid,
  replaceSampleData,
  isAttributeDataMapping,
  Target,
  isAttributeTarget,
} from '../../models';
import {useSampleDataFetcher, useRefreshedSampleDataFetcher} from '../../hooks';
import {AttributeDataMappingDetails} from './AttributeDataMappingDetails';

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
    const column = findColumnByUuid(columns, sources[0]);
    const sampleData =
      sources.length > 0 && null !== column
        ? await sampleDataFetcher(fileKey, column.index, fileStructure.sheet_name, fileStructure.first_product_row)
        : [];
    onDataMappingChange({...dataMapping, sources, sample_data: sampleData});
  };

  const handleTargetChange = (target: Target) => {
    if (isAttributeTarget(target)) {
      onDataMappingChange({...dataMapping, target});
    }
  };

  const handleRefreshSampleData = async (indexToRefresh: number) => {
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
            onTargetChange={handleTargetChange}
            onRefreshSampleData={handleRefreshSampleData}
            onSourcesChange={handleSourcesChange}
          />
        )}
      </Container>
    </DataMappingDetailsContainer>
  );
};

export {DataMappingDetails};
