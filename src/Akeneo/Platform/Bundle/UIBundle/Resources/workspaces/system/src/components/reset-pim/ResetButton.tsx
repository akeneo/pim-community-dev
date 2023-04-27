import React from 'react';
import {Button, useBooleanState} from 'akeneo-design-system';
import {NotificationLevel, useNotify, useRoute, useTranslate} from '@akeneo-pim-community/shared';
import {ResetModal} from './ResetModal';
import {useResetInstance} from '../../hooks';

const ResetButton = () => {
  const [isResetModalOpen, openResetModal, closeResetModal] = useBooleanState(false);
  const notify = useNotify();
  const loginRoute = useRoute('pim_user_security_login');
  const translate = useTranslate();
  const [isLoading, resetInstance] = useResetInstance();

  const handleResetInstance = async () => {
    document.body.style.cursor = 'progress';
    try {
      await resetInstance();
      location.href = loginRoute;
    } catch (e) {
      notify(NotificationLevel.ERROR, translate('pim_system.reset_pim.error_notification'));
    }

    document.body.style.cursor = 'default';
  };

  return (
    <>
      <Button level="danger" ghost={true} disabled={isLoading} onClick={openResetModal}>
        {translate('pim_system.reset_pim.button.label')}
      </Button>
      {isResetModalOpen && (
        <ResetModal canConfirm={!isLoading} onConfirm={handleResetInstance} onCancel={closeResetModal} />
      )}
    </>
  );
};

export {ResetButton};
