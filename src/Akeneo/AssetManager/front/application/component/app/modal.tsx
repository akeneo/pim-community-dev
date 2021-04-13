import React from 'react';
import {AssetCategoriesIllustration, Button, Modal} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

type ConfirmModalProps = {
  titleContent: string;
  content: string;
  confirmButtonText: string;
  onCancel: () => void;
  onConfirm: () => void;
};

const ConfirmModal = ({titleContent, content, confirmButtonText, onCancel, onConfirm}: ConfirmModalProps) => {
  const translate = useTranslate();

  return (
    <Modal onClose={onCancel} closeTitle={translate('pim_common.close')} illustration={<AssetCategoriesIllustration />}>
      <Modal.SectionTitle color="brand">
        {translate('pim_title.akeneo_asset_manager_asset_family_index')}
      </Modal.SectionTitle>
      <Modal.Title>{titleContent}</Modal.Title>
      {content}
      <Modal.BottomButtons>
        <Button level="tertiary" onClick={onCancel}>
          {translate('pim_common.cancel')}
        </Button>
        <Button level="secondary" onClick={onConfirm}>
          {confirmButtonText}
        </Button>
      </Modal.BottomButtons>
    </Modal>
  );
};

export {ConfirmModal};
