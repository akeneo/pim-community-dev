import React from 'react';
import {Button, Modal, DeleteIllustration} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

interface DeleteConfirmationProps {
  deleteAction: () => void;
  cancelDelete: () => void;
}

const DeleteConfirmation = ({deleteAction, cancelDelete}: DeleteConfirmationProps) => {
  const translate = useTranslate();

  return (
    <Modal onClose={cancelDelete} closeTitle={translate('pim_common.close')} illustration={<DeleteIllustration />}>
      <Modal.Title>{translate('pim_common.confirm_deletion')}</Modal.Title>
      <Modal.SectionTitle>{translate('pim_enrich.entity.association_type.module.delete.confirm')}</Modal.SectionTitle>
      <Modal.BottomButtons>
        <Button level="tertiary" onClick={cancelDelete}>
          {translate('pim_common.cancel')}
        </Button>
        <Button level="danger" onClick={deleteAction}>
          {translate('pim_common.confirm')}
        </Button>
      </Modal.BottomButtons>
    </Modal>
  );
};

export {DeleteConfirmation};
