import React, {useState} from 'react';
import styled from 'styled-components';
import {Button, useBooleanState} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {DataMappingDetails, DataMappingDetailsPlaceholder, DataMappingList, InitializeColumnsModal} from './components';
import {Column, createDefaultDataMapping, DataMapping, StructureConfiguration, updateDataMapping} from './models';

type ImportStructureTabProps = {
  structureConfiguration: StructureConfiguration;
  onStructureConfigurationChange: (structureConfiguration: StructureConfiguration) => void;
};

const Container = styled.div`
  display: flex;
  flex-direction: row;
  gap: 40px;
  height: 100%;
`;

const ImportStructureTab = ({structureConfiguration, onStructureConfigurationChange}: ImportStructureTabProps) => {
  const [isInitModalOpen, openInitModal, closeInitModal] = useBooleanState();
  const translate = useTranslate();
  const [selectedDataMappingUuid, setSelectedDataMappingUuid] = useState<string | null>(null);
  const selectedDataMapping =
    structureConfiguration.data_mappings.find(dataMapping => dataMapping.uuid === selectedDataMappingUuid) ?? null;

  const handleConfirm = (generatedColumns: Column[]): void => {
    const dataMapping = createDefaultDataMapping(generatedColumns);
    onStructureConfigurationChange({
      ...structureConfiguration,
      columns: generatedColumns,
      data_mappings: [dataMapping],
    });
    closeInitModal();
  };

  const handleDataMappingChange = (dataMapping: DataMapping) => {
    onStructureConfigurationChange({
      ...structureConfiguration,
      data_mappings: updateDataMapping(structureConfiguration.data_mappings, dataMapping),
    });
  };

  const handleDataMappingAdded = (dataMapping: DataMapping): void => {
    onStructureConfigurationChange({
      ...structureConfiguration,
      data_mappings: [...structureConfiguration.data_mappings, dataMapping],
    });
  };

  const handleDataMappingSelected = (dataMappingSelectedUuid: string) => {
    setSelectedDataMappingUuid(dataMappingSelectedUuid);
  };

  return (
    <>
      {structureConfiguration.columns.length === 0 ? (
        <Button level="primary" onClick={openInitModal}>
          {translate('akeneo.tailored_import.column_initialization.button')}
        </Button>
      ) : (
        <Container>
          <DataMappingList
            dataMappings={structureConfiguration.data_mappings}
            columns={structureConfiguration.columns}
            selectedDataMappingUuid={selectedDataMappingUuid}
            validationErrors={[]}
            onDataMappingAdded={handleDataMappingAdded}
            onDataMappingSelected={handleDataMappingSelected}
          />
          {null === selectedDataMapping ? (
            <DataMappingDetailsPlaceholder />
          ) : (
            <DataMappingDetails
              columns={structureConfiguration.columns}
              dataMapping={selectedDataMapping}
              onDataMappingChange={handleDataMappingChange}
            />
          )}
        </Container>
      )}
      {isInitModalOpen && <InitializeColumnsModal onConfirm={handleConfirm} onCancel={closeInitModal} />}
    </>
  );
};

export type {ImportStructureTabProps};
export {ImportStructureTab};
