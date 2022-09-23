import React, {useCallback, useState} from 'react';
import styled from 'styled-components';
import {DataMappingDetails, DataMappingList, DataMappingDetailsPlaceholder} from './components';
import {
  addDataMapping,
  filterEmptyDataMappings,
  createDataMapping,
  DataMapping,
  updateDataMapping,
} from './models/DataMapping';
import {RequirementCollection} from './models';
import {RequirementsProvider} from './contexts/RequirementsContext';
import {uuid} from 'akeneo-design-system';
import {ErrorBoundary} from './components/shared/ErrorBoundary';

const Container = styled.div`
  padding-top: 10px;
  display: flex;
  gap: 20px;
  height: calc(100vh - 278px);
`;

type DataMappingConfiguratorProps = {
  dataMappings: DataMapping[];
  requirements: RequirementCollection;
  onDataMappingsConfigurationChange: (dataMappings: DataMapping[]) => void;
};

const DataMappingConfigurator = ({
  dataMappings,
  onDataMappingsConfigurationChange,
  requirements,
}: DataMappingConfiguratorProps) => {
  const [selectedRequirementCode, setSelectedRequirementCode] = useState<string | null>(
    dataMappings[0]?.target.name ?? null
  );

  const selectedDataMapping: DataMapping | null =
    dataMappings.find(({target}) => selectedRequirementCode === target.name) ?? null;

  const handleSelectRequirement = useCallback(
    (newlySelectedRequirementCode: string | null) => {
      const newlySelectedDataMapping: DataMapping | null =
        dataMappings.find(({target}) => newlySelectedRequirementCode === target.name) ?? null;
      const newlySelectedRequirement = requirements.find(({code}) => newlySelectedRequirementCode === code) ?? null;

      const cleanedDataMappings = filterEmptyDataMappings(dataMappings);

      // This logic could be moved elsewhere
      if (null === newlySelectedDataMapping) {
        if (null === newlySelectedRequirement) {
          throw new Error('Requirement not found');
        }

        const updatedDataMappings = addDataMapping(
          cleanedDataMappings,
          createDataMapping(newlySelectedRequirement, uuid())
        );
        onDataMappingsConfigurationChange(updatedDataMappings);
      }

      setSelectedRequirementCode(newlySelectedRequirementCode);
    },
    [setSelectedRequirementCode, onDataMappingsConfigurationChange, dataMappings, requirements]
  );

  const handleChangeDataMapping = useCallback(
    (dataMapping: DataMapping) => {
      const updatedDataMappings = updateDataMapping(dataMappings, dataMapping);
      onDataMappingsConfigurationChange(updatedDataMappings);
    },
    [onDataMappingsConfigurationChange, dataMappings]
  );

  return (
    <RequirementsProvider requirements={requirements}>
      <Container>
        <ErrorBoundary>
          <DataMappingList
            selectedRequirement={selectedRequirementCode}
            dataMappings={dataMappings}
            requirements={requirements}
            onDataMappingSelected={handleSelectRequirement}
          />
          {null === selectedDataMapping ? (
            <DataMappingDetailsPlaceholder />
          ) : (
            <DataMappingDetails dataMapping={selectedDataMapping} onDataMappingChange={handleChangeDataMapping} />
          )}
        </ErrorBoundary>
      </Container>
    </RequirementsProvider>
  );
};

export {DataMappingConfigurator};
export type {DataMappingConfiguratorProps};
