import React from 'react';
import {Button, DeleteIllustration, Modal, SectionTitle, Title} from 'akeneo-design-system';
import {NotificationLevel, useNotify, useTranslate, useRouter} from '@akeneo-pim-community/legacy-bridge';

type DeleteModalProps = {
  onCancel: () => void;
  onSuccess: () => void;
  attributeCode: string;
};

const DeleteModal = ({onCancel, onSuccess, attributeCode}: DeleteModalProps) => {
  const translate = useTranslate();
  const notify = useNotify();
  const router = useRouter();

  const handleConfirm = () => {
    fetch(router.generate('pim_enrich_attribute_rest_remove', {'code': attributeCode}), {
      method: 'DELETE',
      headers: new Headers({
        'X-Requested-With': 'XMLHttpRequest',
      }),
    })
      .then(async (response: Response) => {
        if (response.ok) {
          notify(NotificationLevel.SUCCESS, translate('pim_enrich.entity.attribute.flash.delete.success'));
          onSuccess();
        } else {
          notify(NotificationLevel.ERROR, (await response.json()).message ?? translate('pim_enrich.entity.attribute.flash.delete.fail'));
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
