import React from 'react';
import {Button, useBooleanState} from 'akeneo-design-system';
import {NotificationLevel, useNotify, useTranslate} from '@akeneo-pim-community/shared';
import {ResetModal} from './ResetModal';
import {useCheckInstanceCanBeReset} from '../../hooks';

const ResetButton = () => {
  const translate = useTranslate();
  const notify = useNotify();
  const [isResetModalOpen, openResetModal, closeResetModal] = useBooleanState(false);
  const [isLoading, checkInstanceCanBeReset] = useCheckInstanceCanBeReset();

  const handleOpenModal = async () => {
    try {
      await checkInstanceCanBeReset();

      openResetModal();
    } catch (error) {
      notify(NotificationLevel.ERROR, translate('pim_system.reset_pim.jobs_running'));
    }
  };

  return (
    <>
      <Button level="danger" ghost={true} disabled={isLoading} onClick={handleOpenModal}>
        {translate('pim_system.reset_pim.button.label')}
      </Button>
      {isResetModalOpen && <ResetModal onConfirm={closeResetModal} onCancel={closeResetModal} />}
    </>
  );
};

export {ResetButton};
