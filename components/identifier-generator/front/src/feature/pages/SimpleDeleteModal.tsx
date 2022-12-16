import React, {FC} from 'react';
import {Button, DeleteIllustration, Modal} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';

type SimpleDeleteModalProps = {
  onClose: () => void;
  onDelete: () => void;
};

const SimpleDeleteModal: FC<SimpleDeleteModalProps> = ({onClose, onDelete}) => {
  const translate = useTranslate();

  return (
    <Modal closeTitle={translate('pim_common.close')} illustration={<DeleteIllustration />} onClose={onClose}>
      <Modal.SectionTitle color="brand">{translate('pim_identifier_generator.deletion.operations')}</Modal.SectionTitle>
      <Modal.Title>{translate('pim_common.confirm_deletion')}</Modal.Title>
      {translate('pim_identifier_generator.list.confirmation')}
      <Modal.BottomButtons>
        <Button onClick={onClose} level="tertiary">
          {translate('pim_common.cancel')}
        </Button>
        <Button level="danger" onClick={onDelete}>
          {translate('pim_common.delete')}
        </Button>
      </Modal.BottomButtons>
    </Modal>
  );
};

export {SimpleDeleteModal};
