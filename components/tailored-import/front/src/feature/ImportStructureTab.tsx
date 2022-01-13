import React from 'react';
import {Button, useBooleanState} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {InitializeColumnsModal} from './components';
import {Column, StructureConfiguration} from './models';
import {SourceDropdown} from './components';

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

  return (
    <>
      <Button level="primary" onClick={openInitModal}>
        {translate('akeneo.tailored_import.column_initialization.button')}
      </Button>
      {isInitModalOpen && <InitializeColumnsModal onConfirm={handleConfirm} onCancel={closeInitModal} />}
      <SourceDropdown columns={structureConfiguration.columns} onColumnSelected={handleColumnSelected} />
    </>
  );
};

export type {ImportStructureTabProps};
export {ImportStructureTab};
