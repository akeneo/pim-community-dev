import React, {FC, useRef, useState} from 'react';
import {Button, Field, Helper, Modal, ProductCategoryIllustration, TextInput, useAutoFocus} from 'akeneo-design-system';
import {
  NotificationLevel,
  TextField,
  useNotify,
  useRouter,
  useTranslate,
  useUserContext,
} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {createCategory, ValidationErrors} from '../infrastructure';

type NewCategoryModalProps = {
  closeModal: () => void;
  onCreate: () => void;
  parentCode?: string;
};

const NewCategoryModal: FC<NewCategoryModalProps> = ({closeModal, onCreate, parentCode}) => {
  const router = useRouter();
  const translate = useTranslate();
  const [newCategoryCode, setNewCategoryCode] = useState('');
  const [newCategoryLabel, setNewCategoryLabel] = useState('');
  const notify = useNotify();
  const [validationErrors, setValidationErrors] = useState<ValidationErrors>({});
  const locale = useUserContext().get('catalogLocale');

  const codeFieldRef = useRef<HTMLInputElement | null>(null);
  useAutoFocus(codeFieldRef);

  const createNewCategoryTree = async () => {
    if (newCategoryCode.trim() !== '') {
      const errors = await createCategory(router, newCategoryCode, parentCode, locale, newCategoryLabel);
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

      <StyledField label={translate('pim_common.code')} requiredLabel={translate('pim_common.required_label')}>
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
      <TextFieldContainer>
        <TextField
          label={translate('pim_common.label')}
          value={newCategoryLabel}
          onChange={setNewCategoryLabel}
          locale={locale}
          maxLength={100}
        />
      </TextFieldContainer>
    </Modal>
  );
};

const StyledField = styled(Field)`
  margin-top: 15px;
`;

const TextFieldContainer = styled.div`
  margin-top: 15px;
`;

export {NewCategoryModal};
