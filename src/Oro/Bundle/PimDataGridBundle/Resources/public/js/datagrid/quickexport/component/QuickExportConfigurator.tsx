import React from 'react';
import {DependenciesProvider, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {AkeneoThemeProvider, Modal, useToggleState, ModalCloseButton} from '@akeneo-pim-community/shared';

type QuickExportConfiguratorProps = {
  onActionLaunch: (actionName: string) => void;
};

const QuickExportConfigurator = (props: QuickExportConfiguratorProps) => (
  <DependenciesProvider>
    <AkeneoThemeProvider>
      <QuickExportConfiguratorContainer {...props} />
    </AkeneoThemeProvider>
  </DependenciesProvider>
);

const QuickExportConfiguratorContainer = ({onActionLaunch}: QuickExportConfiguratorProps) => {
  const [isModalOpen, openModal, closeModal] = useToggleState(false);
  const translate = useTranslate();

  return (
    <div onClick={openModal}>
      {translate('pim_datagrid.mass_action_group.quick_export.label')}
      {isModalOpen && (
        <Modal>
          <ModalCloseButton onClick={() => closeModal()} />
          <div
            onClick={() => {
              closeModal();
              onActionLaunch('yolo');
            }}
          >
            yolo
          </div>
        </Modal>
      )}
    </div>
  );
};

export {QuickExportConfigurator};
