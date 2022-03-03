import React, {useState} from 'react';
import styled from 'styled-components';
import {filterErrors, ValidationError} from '@akeneo-pim-community/shared';
import {
  DataMappingDetails,
  DataMappingDetailsPlaceholder,
  DataMappingList,
  InitializeFileStructure,
} from './components';
import {Column, createDefaultDataMapping, DataMapping, StructureConfiguration, updateDataMapping} from './models';
import {useFetchers} from './contexts';

const Container = styled.div`
  display: flex;
  flex-direction: row;
  gap: 40px;
  height: 100%;
`;

type ImportStructureTabProps = {
  structureConfiguration: StructureConfiguration;
  validationErrors: ValidationError[];
  onStructureConfigurationChange: (structureConfiguration: StructureConfiguration) => void;
};

const ImportStructureTab = ({
  structureConfiguration,
  validationErrors,
  onStructureConfigurationChange,
}: ImportStructureTabProps) => {
  const [selectedDataMappingUuid, setSelectedDataMappingUuid] = useState<string | null>(null);
  const selectedDataMapping =
    structureConfiguration.import_structure.data_mappings.find(
      dataMapping => dataMapping.uuid === selectedDataMappingUuid
    ) ?? null;
  const attributeFetcher = useFetchers().attribute;

  const handleFileStructureInitialized = async (fileKey: string, columns: Column[]): Promise<void> => {
    const attributeIdentifier = await attributeFetcher.fetchAttributeIdentifier();

    if (attributeIdentifier) {
      const dataMapping = createDefaultDataMapping(attributeIdentifier, columns);
      onStructureConfigurationChange({
        ...structureConfiguration,
        import_structure: {
          ...structureConfiguration.import_structure,
          columns,
          data_mappings: [dataMapping],
        },
        file_key: fileKey,
      });
    }
  };

  const handleDataMappingChange = (dataMapping: DataMapping) => {
    onStructureConfigurationChange({
      ...structureConfiguration,
      import_structure: {
        ...structureConfiguration.import_structure,
        data_mappings: updateDataMapping(structureConfiguration.import_structure.data_mappings, dataMapping),
      },
    });
  };

  const handleDataMappingSelected = (dataMappingSelectedUuid: string) => {
    setSelectedDataMappingUuid(dataMappingSelectedUuid);
  };

  const handleDataMappingAdded = (dataMapping: DataMapping): void => {
    onStructureConfigurationChange({
      ...structureConfiguration,
      import_structure: {
        ...structureConfiguration.import_structure,
        data_mappings: [...structureConfiguration.import_structure.data_mappings, dataMapping],
      },
    });
    handleDataMappingSelected(dataMapping.uuid);
  };

  return (
    <>
      {null === structureConfiguration.file_key ? (
        <InitializeFileStructure onConfirm={handleFileStructureInitialized} />
      ) : (
        <Container>
          <DataMappingList
            dataMappings={structureConfiguration.import_structure.data_mappings}
            columns={structureConfiguration.import_structure.columns}
            selectedDataMappingUuid={selectedDataMappingUuid}
            validationErrors={filterErrors(validationErrors, `[data_mappings]`)}
            onDataMappingAdded={handleDataMappingAdded}
            onDataMappingSelected={handleDataMappingSelected}
          />
          {null === selectedDataMapping ? (
            <DataMappingDetailsPlaceholder />
          ) : (
            <DataMappingDetails
              columns={structureConfiguration.import_structure.columns}
              dataMapping={selectedDataMapping}
              validationErrors={filterErrors(validationErrors, `[data_mappings][${selectedDataMapping.uuid}]`)}
              onDataMappingChange={handleDataMappingChange}
            />
          )}
        </Container>
      )}
    </>
  );
};

export type {ImportStructureTabProps};
export {ImportStructureTab};
