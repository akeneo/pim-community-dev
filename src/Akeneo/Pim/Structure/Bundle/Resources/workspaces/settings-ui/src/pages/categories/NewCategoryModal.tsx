import React, {FC, useRef, useState} from 'react';
import {Button, Field, Helper, Modal, ProductCategoryIllustration, TextInput, useAutoFocus} from 'akeneo-design-system';
import {NotificationLevel, useNotify, useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {createCategory, ValidationErrors} from '../../infrastructure/savers';

type NewCategoryModalProps = {
  closeModal: () => void;
  onCreate: () => void;
  parentCode?: string;
};

const NewCategoryModal: FC<NewCategoryModalProps> = ({closeModal, onCreate, parentCode}) => {
  const translate = useTranslate();
  const [newCategoryCode, setNewCategoryCode] = useState('');
  const notify = useNotify();
  const [validationErrors, setValidationErrors] = useState<ValidationErrors>({});

  const codeFieldRef = useRef<HTMLInputElement | null>(null);
  useAutoFocus(codeFieldRef);

  const createNewCategoryTree = async () => {
    if (newCategoryCode.trim() !== '') {
      const errors = await createCategory(newCategoryCode, parentCode);
      if (Object.keys(errors).length > 0) {
        setValidationErrors(errors);
        notify(
          NotificationLevel.ERROR,
          translate(
            parentCode === undefined
              ? 'pim_enrich.entity.category.category_tree_creation.error'
              : 'pim_enrich.entity.category.category_creation_error',
            {code: newCategoryCode}
          )
        );
        return;
      }
    }

    setValidationErrors({});
    onCreate();
    closeModal();
    notify(
      NotificationLevel.SUCCESS,
      translate(
        parentCode === undefined
          ? 'pim_enrich.entity.category.category_tree_creation.success'
          : 'pim_enrich.entity.category.category_created',
        {code: newCategoryCode}
      )
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
      <Modal.Title>
        {translate(
          parentCode === undefined
            ? 'pim_enrich.entity.category.new_category_tree'
            : 'pim_enrich.entity.category.new_category'
        )}
      </Modal.Title>

      <StyledField label={translate('pim_common.code')}>
        <TextInput
          ref={codeFieldRef}
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
