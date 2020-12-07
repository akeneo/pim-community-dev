import React from 'react';
import {Button, DeleteIllustration, Modal, SectionTitle, Title} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

type DeleteModalProps = {
  isOpen: boolean;
  onClose: () => void;
  onConfirm: () => void;
}

const DeleteModal = ({isOpen, onClose, onConfirm}: DeleteModalProps) => {
  const translate = useTranslate();

  return (
    <Modal
      isOpen={isOpen}
      onClose={onClose}
      closeTitle={translate('pim_common.close')}
      illustration={<DeleteIllustration/>}
      role="dialog"
    >
      <SectionTitle color="brand">{translate('pim_enrich.entity.attribute.plural_label')}</SectionTitle>
      <Title>{translate('pim_common.confirm_deletion')}</Title>
      <div>{translate('pim_enrich.entity.attribute.module.delete.confirm')}</div>
      <Modal.BottomButtons>
        <Button level="tertiary" onClick={onClose}>
          {translate('pim_common.cancel')}
        </Button>
        <Button level="danger" onClick={onConfirm}>
          {translate('pim_common.delete')}
        </Button>
      </Modal.BottomButtons>
    </Modal>
  )
};

export {DeleteModal};
