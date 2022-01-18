import React from 'react';
import {Button, useBooleanState} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {InitializeColumnsModal, AddDataMappingDropdown} from './components';
import {Column, DataMapping, MAX_DATA_MAPPING_COUNT, StructureConfiguration} from './models';
import {SourceDropdown} from './components';
import {DataMappingList} from './components/DataMappingList/DataMappingList';

type ImportStructureTabProps = {
  structureConfiguration: StructureConfiguration;
  onStructureConfigurationChange: (structureConfiguration: StructureConfiguration) => void;
};

const ImportStructureTab = ({structureConfiguration, onStructureConfigurationChange}: ImportStructureTabProps) => {
  const [isInitModalOpen, openInitModal, closeInitModal] = useBooleanState();
  const translate = useTranslate();

  const handleConfirm = (generatedColumns: Column[]): void => {
    onStructureConfigurationChange({...structureConfiguration, columns: generatedColumns});
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

  const canAddDataMapping = MAX_DATA_MAPPING_COUNT > structureConfiguration.dataMappings.length;

  return (
    <>
      <Button level="primary" onClick={openInitModal}>
        {translate('akeneo.tailored_import.column_initialization.button')}
      </Button>
      {isInitModalOpen && <InitializeColumnsModal onConfirm={handleConfirm} onCancel={closeInitModal} />}
      <SourceDropdown columns={structureConfiguration.columns} onColumnSelected={handleColumnSelected} />
      <AddDataMappingDropdown canAddDataMapping={canAddDataMapping} onDataMappingAdded={handleDataMappingAdded} />
      <DataMappingList dataMappings={structureConfiguration.dataMappings} globalErrors={[]} />
    </>
  );
};

export type {ImportStructureTabProps};
export {ImportStructureTab};
