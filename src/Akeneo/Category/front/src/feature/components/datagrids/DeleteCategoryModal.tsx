import React, {FC, useEffect, useState} from 'react';
import {Button, DeleteIllustration, getFontSize, Helper, Modal} from 'akeneo-design-system';
import {useFeatureFlags, useIsMounted, useTranslate} from '@akeneo-pim-community/shared';
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
  const isMounted = useIsMounted();
  const translate = useTranslate();
  const featureFlags = useFeatureFlags();

  const {data: categoryChildrenCount, isLoading} = useCountCategoryChildren(categoryId);

  let warning = null;
  if (featureFlags.isEnabled('enriched_category') && !isLoading) {
    if (categoryChildrenCount && categoryChildrenCount > 0) {
      warning = translate('pim_enrich.entity.category.category_tree_deletion.warning_categories_number', {
        categoriesNumber: categoryChildrenCount,
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
      <Message>{translate(message, {name: categoryLabel})}</Message>
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
  margin-top: 15px;

  button:first-child {
    margin-right: 10px;
  }
`;

export {DeleteCategoryModal};
