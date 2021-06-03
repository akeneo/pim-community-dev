import React, {FC} from 'react';
import {Button, DeleteIllustration, getFontSize, Modal} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';

type DeleteCategoryModalProps = {
  categoryLabel: string;
  closeModal: () => void;
  deleteCategory: () => void;
  message: string;
};

const DeleteCategoryModal: FC<DeleteCategoryModalProps> = ({categoryLabel, closeModal, deleteCategory, message}) => {
  const translate = useTranslate();

  return (
    <Modal closeTitle="Close" onClose={closeModal} illustration={<DeleteIllustration />}>
      <Modal.SectionTitle color="brand">{translate('pim_enrich.entity.category.plural_label')}</Modal.SectionTitle>
      <Modal.Title>{translate('pim_common.confirm_deletion')}</Modal.Title>

      <Message>{translate(message, {name: categoryLabel})}</Message>
      <ActionButtons>
        <Button onClick={closeModal} level="tertiary">
          {translate('pim_common.cancel')}
        </Button>
        <Button onClick={() => deleteCategory()} level="danger" className="ok">
          {translate('pim_common.delete')}
        </Button>
      </ActionButtons>
    </Modal>
  );
};

const Message = styled.p`
  font-size: ${getFontSize('big')};
`;

const ActionButtons = styled.p`
  margin-top: 15px;

  button:first-child {
    margin-right: 10px;
  }
`;

export {DeleteCategoryModal};
