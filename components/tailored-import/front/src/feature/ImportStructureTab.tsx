import React, {useEffect, useState} from 'react';
import styled from 'styled-components';
import {filterErrors, ValidationError} from '@akeneo-pim-community/shared';
import {
  DataMappingDetails,
  DataMappingDetailsPlaceholder,
  DataMappingList,
  InitializeFileStructure,
} from './components';
import {
  Column,
  createDefaultDataMapping,
  DataMapping,
  FileStructure,
  StructureConfiguration,
  updateDataMapping,
} from './models';
import {useFetchers} from './contexts';
import {useSampleDataFetcher} from './hooks';

const Container = styled.div`
  padding-top: 10px;
  display: flex;
  flex-direction: row;
  gap: 40px;
  height: calc(100vh - 278px);
`;

type ImportStructureTabProps = {
  structureConfiguration: StructureConfiguration;
  validationErrors: ValidationError[];
  onStructureConfigurationChange: (structureConfiguration: StructureConfiguration) => void;
};

const ImportStructureTab = ({
  structureConfiguration: initialStructureConfiguration,
  validationErrors,
  onStructureConfigurationChange,
}: ImportStructureTabProps) => {
  const [structureConfiguration, setStructureConfiguration] =
    useState<StructureConfiguration>(initialStructureConfiguration);
  const [selectedDataMappingUuid, setSelectedDataMappingUuid] = useState<string | null>(null);
  const selectedDataMapping =
    structureConfiguration.import_structure.data_mappings.find(
      dataMapping => dataMapping.uuid === selectedDataMappingUuid
    ) ?? null;
  const attributeFetcher = useFetchers().attribute;
  const sampleDataFetcher = useSampleDataFetcher();

  useEffect(() => {
    onStructureConfigurationChange(structureConfiguration);
  }, [onStructureConfigurationChange, structureConfiguration]);

  const handleFileStructureInitialized = async (
    fileKey: string,
    columns: Column[],
    identifierColumn: Column | null,
    fileStructure: FileStructure
  ): Promise<void> => {
    const attributeIdentifier = await attributeFetcher.fetchAttributeIdentifier();

    if (attributeIdentifier) {
      const sampleData =
        null !== identifierColumn
          ? await sampleDataFetcher(
              fileKey,
              [identifierColumn.index],
              fileStructure.sheet_name,
              fileStructure.first_product_row
            )
          : [];

      const dataMapping = createDefaultDataMapping(attributeIdentifier, identifierColumn, sampleData);

      setStructureConfiguration(structureConfiguration => ({
        ...structureConfiguration,
        import_structure: {
          ...structureConfiguration.import_structure,
          columns,
          data_mappings: [dataMapping],
        },
        file_key: fileKey,
        file_structure: fileStructure,
      }));
    }
  };

  const handleDataMappingChange = (dataMapping: DataMapping) => {
    setStructureConfiguration({
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
    setStructureConfiguration({
      ...structureConfiguration,
      import_structure: {
        ...structureConfiguration.import_structure,
        data_mappings: [...structureConfiguration.import_structure.data_mappings, dataMapping],
      },
    });
    handleDataMappingSelected(dataMapping.uuid);
  };

  const handleDataMappingRemoved = (dataMappingUuid: string): void => {
    setStructureConfiguration({
      ...structureConfiguration,
      import_structure: {
        ...structureConfiguration.import_structure,
        data_mappings: structureConfiguration.import_structure.data_mappings.filter(
          dataMapping => dataMapping.uuid !== dataMappingUuid
        ),
      },
    });
  };

  return (
    <>
      {null === structureConfiguration.file_key || 0 === structureConfiguration.import_structure.columns.length ? (
        <InitializeFileStructure
          initialFileKey={structureConfiguration.file_key}
          onConfirm={handleFileStructureInitialized}
        />
      ) : (
        <Container>
          <DataMappingList
            dataMappings={structureConfiguration.import_structure.data_mappings}
            columns={structureConfiguration.import_structure.columns}
            selectedDataMappingUuid={selectedDataMappingUuid}
            validationErrors={filterErrors(validationErrors, `[data_mappings]`)}
            onDataMappingAdded={handleDataMappingAdded}
            onDataMappingSelected={handleDataMappingSelected}
            onDataMappingRemoved={handleDataMappingRemoved}
          />
          {null === selectedDataMapping ? (
            <DataMappingDetailsPlaceholder />
          ) : (
            <DataMappingDetails
              fileKey={structureConfiguration.file_key}
              fileStructure={structureConfiguration.file_structure}
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
