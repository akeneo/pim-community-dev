import React, {useState} from 'react';
import styled from 'styled-components';
import {SectionTitle} from 'akeneo-design-system';
import {filterErrors, useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {Column, DataMapping, Target, ColumnIdentifier, FileStructure, getColumnByUuid} from '../../models';
import {Sources} from './Sources';
import {TargetParameters} from './TargetParameters';
import {Operations} from './Operations';
import {useFetchSampleData} from '../../hooks/useFetchSampleData';

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
  const [selectedSources, setSelectedSources] = useState<ColumnIdentifier[]>();
  const fetchSampleData = useFetchSampleData();

  const handleSourcesChange = async (sources: ColumnIdentifier[]) => {
    setSelectedSources(sources);
    let sample_data: Array<string>;
    const column = getColumnByUuid(columns, sources[0]);
    if (sources.length > 0 && null !== column) {
      sample_data = await fetchSampleData(
        fileKey,
        column.index,
        fileStructure.sheet_name,
        fileStructure.first_product_row
      );
    } else {
      sample_data = [];
    }
    onDataMappingChange({...dataMapping, sources, sample_data});
  };

  const handleTargetParametersChange = (target: Target) => {
    onDataMappingChange({...dataMapping, target});
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
        <Operations dataMapping={dataMapping} />
      </Container>
    </DataMappingDetailsContainer>
  );
};

export {DataMappingDetails};
