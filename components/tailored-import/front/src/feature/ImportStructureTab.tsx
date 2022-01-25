import React from 'react';
import {Button, useBooleanState} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {InitializeColumnsModal} from './components';
import {Column, createDefaultDataMapping, DataMapping, StructureConfiguration} from './models';
import {SourceDropdown, DataMappingList} from './components';

type ImportStructureTabProps = {
  structureConfiguration: StructureConfiguration;
  onStructureConfigurationChange: (structureConfiguration: StructureConfiguration) => void;
};

const ImportStructureTab = ({structureConfiguration, onStructureConfigurationChange}: ImportStructureTabProps) => {
  const [isInitModalOpen, openInitModal, closeInitModal] = useBooleanState();
  const translate = useTranslate();

  const handleConfirm = (generatedColumns: Column[]): void => {
    const dataMapping = createDefaultDataMapping(generatedColumns);
    onStructureConfigurationChange({...structureConfiguration, columns: generatedColumns, dataMappings: [dataMapping]});
    closeInitModal();
  };

  /* istanbul ignore next */
  const handleColumnSelected = (column: Column): void => {};

  const handleDataMappingAdded = (dataMapping: DataMapping): void => {
    onStructureConfigurationChange({
      ...structureConfiguration,
      dataMappings: [...structureConfiguration.dataMappings, dataMapping],
    });
  };

  return (
    <>
      <Button level="primary" onClick={openInitModal}>
        {translate('akeneo.tailored_import.column_initialization.button')}
      </Button>
      {isInitModalOpen && <InitializeColumnsModal onConfirm={handleConfirm} onCancel={closeInitModal} />}
      <SourceDropdown columns={structureConfiguration.columns} onColumnSelected={handleColumnSelected} />
      <DataMappingList
        dataMappings={structureConfiguration.dataMappings}
        columns={structureConfiguration.columns}
        validationErrors={[]}
        onDataMappingAdded={handleDataMappingAdded}
      />
    </>
  );
};

export type {ImportStructureTabProps};
export {ImportStructureTab};
