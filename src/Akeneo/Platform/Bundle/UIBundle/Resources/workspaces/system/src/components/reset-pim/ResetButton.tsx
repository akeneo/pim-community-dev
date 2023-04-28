import React from 'react';
import {Button, useBooleanState} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {ResetModal} from './ResetModal';

const ResetButton = () => {
  const translate = useTranslate();
  const [isResetModalOpen, openResetModal, closeResetModal] = useBooleanState(false);

  return (
    <>
      <Button level="danger" ghost={true} onClick={openResetModal}>
        {translate('pim_system.reset_pim.button.label')}
      </Button>
      {isResetModalOpen && <ResetModal onConfirm={closeResetModal} onCancel={closeResetModal} />}
    </>
  );
};

export {ResetButton};
