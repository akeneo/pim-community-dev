import React from 'react';
import {Button, DeleteIllustration, Modal, SectionTitle, Title} from 'akeneo-design-system';
import {NotificationLevel, useNotify, useTranslate} from '@akeneo-pim-community/legacy-bridge';

type DeleteModalProps = {
  onCancel: () => void;
  onSuccess: () => void;
  deleteUrl: string;
};

const DeleteModal = ({onCancel, onSuccess, deleteUrl}: DeleteModalProps) => {
  const translate = useTranslate();
  const notify = useNotify();

  const handleConfirm = () => {
    fetch(deleteUrl, {
      method: 'DELETE',
      headers: new Headers({
        'X-Requested-With': 'XMLHttpRequest',
      }),
    })
      .then((response: Response) => {
        if (response.ok) {
          notify(NotificationLevel.SUCCESS, translate('pim_enrich.entity.attribute.flash.delete.success'));
          onSuccess();
        } else {
          notify(NotificationLevel.ERROR, translate('pim_enrich.entity.attribute.flash.delete.fail'));
        }
      })
      .catch(() => {
        notify(NotificationLevel.ERROR, translate('pim_enrich.entity.attribute.flash.delete.fail'));
      });
  };

  return (
    <Modal
      isOpen={true}
      onClose={onCancel}
      closeTitle={translate('pim_common.close')}
      illustration={<DeleteIllustration />}
      role="dialog"
    >
      <SectionTitle color="brand">{translate('pim_enrich.entity.attribute.plural_label')}</SectionTitle>
      <Title>{translate('pim_common.confirm_deletion')}</Title>
      <div>{translate('pim_enrich.entity.attribute.module.delete.confirm')}</div>
      <Modal.BottomButtons>
        <Button level="tertiary" onClick={onCancel}>
          {translate('pim_common.cancel')}
        </Button>
        <Button level="danger" onClick={handleConfirm}>
          {translate('pim_common.delete')}
        </Button>
      </Modal.BottomButtons>
    </Modal>
  );
};

export {DeleteModal};
