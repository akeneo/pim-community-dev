import React from 'react';
import {DeleteIllustration, Button, Modal, SectionTitle, Title} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy';

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
      <SectionTitle color="brand">{translate('measurements.title.measurement')}</SectionTitle>
      <Title>{translate('pim_common.confirm_deletion')}</Title>
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
