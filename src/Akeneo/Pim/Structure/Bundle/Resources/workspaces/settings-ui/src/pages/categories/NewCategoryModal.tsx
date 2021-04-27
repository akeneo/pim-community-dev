import React, {FC, useState} from 'react';
import {Button, Field, Helper, Modal, ProductCategoryIllustration, TextInput} from 'akeneo-design-system';
import {NotificationLevel, useNotify, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import styled from 'styled-components';
import {saveNewCategoryTree, ValidationErrors} from '../../infrastructure/savers';

type NewCategoryModalProps = {
  closeModal: () => void;
  refreshCategoryTrees: () => void;
};

const NewCategoryModal: FC<NewCategoryModalProps> = ({closeModal, refreshCategoryTrees}) => {
  const translate = useTranslate();
  const [newCategoryCode, setNewCategoryCode] = useState('');
  const notify = useNotify();
  const [validationErrors, setValidationErrors] = useState<ValidationErrors>({});

  const createNewCategoryTree = async () => {
    if (newCategoryCode.trim() !== '') {
      const errors = await saveNewCategoryTree(newCategoryCode);
      if (Object.keys(errors).length > 0) {
        setValidationErrors(errors);
        notify(
          NotificationLevel.ERROR,
          translate('pim_enrich.entity.category.category_tree_creation_error', {tree: newCategoryCode})
        );
        return;
      }
    }

    refreshCategoryTrees();
    setValidationErrors({});
    closeModal();
    notify(
      NotificationLevel.SUCCESS,
      translate('pim_enrich.entity.category.category_tree_created', {tree: newCategoryCode})
    );
  };

  return (
    <Modal closeTitle="Close" onClose={closeModal} illustration={<ProductCategoryIllustration />}>
      <Modal.TopRightButtons>
        <Button level="primary" onClick={createNewCategoryTree} disabled={newCategoryCode.trim() === ''}>
          {translate('pim_common.create')}
        </Button>
      </Modal.TopRightButtons>
      <Modal.SectionTitle color="brand">{translate('pim_enrich.entity.category.plural_label')}</Modal.SectionTitle>
      <Modal.Title>{translate('new category')}</Modal.Title>

      <StyledField label={translate('pim_common.code')}>
        <TextInput
          value={newCategoryCode}
          onChange={setNewCategoryCode}
          placeholder={translate('pim_common.code')}
          maxLength={100}
        />
        {validationErrors.hasOwnProperty('code') && (
          <Helper inline={true} level="error">
            {validationErrors['code']}
          </Helper>
        )}
      </StyledField>
    </Modal>
  );
};

const StyledField = styled(Field)`
  margin-top: 15px;
`;

export {NewCategoryModal};
