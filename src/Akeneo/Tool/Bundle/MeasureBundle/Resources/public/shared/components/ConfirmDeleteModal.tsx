import React from 'react';
import {DeleteIllustration, Button, Modal} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

type ConfirmModalProps = {
  isOpen: boolean;
  description: string;
  onConfirm: () => void;
  onCancel: () => void;
};

const ConfirmDeleteModal = ({isOpen, description, onConfirm, onCancel}: ConfirmModalProps) => {
  const translate = useTranslate();

  if (!isOpen) return null;

  return (
    <Modal closeTitle={translate('pim_common.close')} onClose={onCancel} illustration={<DeleteIllustration />}>
      <Modal.SectionTitle color="brand">{translate('measurements.title.measurement')}</Modal.SectionTitle>
      <Modal.Title>{translate('pim_common.confirm_deletion')}</Modal.Title>
      {description}
      <Modal.BottomButtons>
        <Button level="tertiary" onClick={onCancel}>
          {translate('pim_common.cancel')}
        </Button>
        <Button level="danger" onClick={onConfirm}>
          {translate('pim_common.delete')}
        </Button>
      </Modal.BottomButtons>
    </Modal>
  );
};

export {ConfirmDeleteModal};
