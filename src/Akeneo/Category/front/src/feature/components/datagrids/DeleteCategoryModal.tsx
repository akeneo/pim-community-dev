import React, {FC} from 'react';
import {Button, DeleteIllustration, getFontSize, Helper, Modal} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {useCountCategoryChildren} from '../../hooks/useCountCategoryChildren';

type DeleteCategoryModalProps = {
  categoryLabel: string;
  closeModal: () => void;
  deleteCategory: () => void;
  message: string;
  numberOfProducts?: number;
  categoryId: number;
};

const DeleteCategoryModal: FC<DeleteCategoryModalProps> = ({
  categoryLabel,
  closeModal,
  deleteCategory,
  message,
  numberOfProducts,
  categoryId,
}) => {
  const translate = useTranslate();

  const {data: categoryChildrenCount, isLoading} = useCountCategoryChildren(categoryId);
  let warning = null;
  if (!isLoading) {
    if (categoryChildrenCount && categoryChildrenCount > 0) {
      warning = translate('pim_enrich.entity.category.category_tree_deletion.warning_categories_number', {
        // Add the current category (parent) we want to delete
        categoriesNumber: categoryChildrenCount + 1,
      });
    } else if (numberOfProducts) {
      warning = translate('pim_enrich.entity.category.category_tree_deletion.warning_products', {
        name: categoryLabel,
      });
    }
  }

  return (
    <Modal closeTitle="Close" onClose={closeModal} illustration={<DeleteIllustration />}>
      <Modal.SectionTitle color="brand">{translate('pim_enrich.entity.category.plural_label')}</Modal.SectionTitle>
      <Modal.Title>{translate('pim_common.confirm_deletion')}</Modal.Title>
      <MessageContainer>{translate(message, {name: categoryLabel})}</MessageContainer>
      {warning && <Helper level="error">{warning}</Helper>}
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
  margin-top: 20px;

  button:first-child {
    margin-right: 10px;
  }
`;

const MessageContainer = styled(Message)`
  margin-bottom: 20px;
`;

export {DeleteCategoryModal};
