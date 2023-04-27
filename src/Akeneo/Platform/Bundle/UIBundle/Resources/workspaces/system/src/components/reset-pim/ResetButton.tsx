import React from 'react';
import {Button, useBooleanState} from 'akeneo-design-system';
import {useRoute, useTranslate} from '@akeneo-pim-community/shared';
import {ResetModal} from './ResetModal';
import {useResetInstance} from '../../hooks';

const ResetButton = () => {
  const translate = useTranslate();
  const resetInstance = useResetInstance();
  const loginRoute = useRoute('pim_user_security_login');
  const [isResetModalOpen, openResetModal, closeResetModal] = useBooleanState(false);

  const handleResetInstance = async () => {
    document.body.style.cursor = 'progress';
    await resetInstance();
    document.body.style.cursor = 'default';
    closeResetModal();
    location.href = loginRoute;
  };

  return (
    <>
      <Button level="danger" ghost={true} onClick={openResetModal}>
        {translate('pim_system.reset_pim.button.label')}
      </Button>
      {isResetModalOpen && <ResetModal onConfirm={handleResetInstance} onCancel={closeResetModal} />}
    </>
  );
};

export {ResetButton};
