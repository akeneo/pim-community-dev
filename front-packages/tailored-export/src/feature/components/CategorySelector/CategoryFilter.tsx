import React, {useState} from 'react';
import styled from 'styled-components';
import {BooleanInput, Button, Field, Modal, ProductCategoryIllustration, useBooleanState} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {MultiCategoryTreeSelector} from './MultiCategoryTreeSelector';

const Container = styled.div`
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
`;
type Operator = 'IN CHILDREN LIST' | 'IN' | 'NOT IN';
// How to export component CategoryFilter + type CategoryFilter (same name)
type CategoryFilterType = {
  field: 'categories';
  operator: Operator;
  value: string[];
};
type CategoryFilterProps = {
  filter: CategoryFilterType;
  onChange: (updatedFilter: CategoryFilterType) => void;
};

const CategoryFilter = ({filter, onChange}: CategoryFilterProps) => {
  const translate = useTranslate();
  const [isCategoriesModalOpen, openCategoriesModal, closeCategoriesModal] = useBooleanState();
  const [categorySelection, setSelectedCategories] = useState<string[]>(filter.value);
  const [operator, setOperator] = useState<Operator>(filter.operator);
  const handleShouldIndludeSubCategoryChange = (updatedValue: boolean) => {
    const newOperator = updatedValue ? 'IN CHILDREN LIST' : filter.value.length === 0 ? 'NOT IN' : 'IN';
    setOperator(newOperator);
  };
  const handleConfirm = () => {
    onChange({...filter, operator, value: categorySelection});
    closeCategoriesModal();
  };
  const handleClose = () => {
    setSelectedCategories(filter.value);
    setOperator(filter.operator);
    closeCategoriesModal();
  };

  return (
    <Container>
      {isCategoriesModalOpen && (
        <Modal
          closeTitle={translate('pim_common.close')}
          onClose={handleClose}
          illustration={<ProductCategoryIllustration />}
        >
          <Modal.Title>{translate('pim_connector.export.categories.selector.modal.title')}</Modal.Title>
          <Field label={translate('jstree.include_sub')}>
            <BooleanInput
              noLabel={translate('pim_common.no')}
              yesLabel={translate('pim_common.yes')}
              value={operator === 'IN CHILDREN LIST'}
              clearLabel={translate('pim_common.clear_value')}
              readOnly={false}
              onChange={handleShouldIndludeSubCategoryChange}
            />
          </Field>
          <MultiCategoryTreeSelector
            categorySelection={categorySelection}
            onCategorySelection={setSelectedCategories}
          />
          <Modal.BottomButtons>
            <Button level="tertiary" onClick={handleClose}>
              {translate('pim_common.cancel')}
            </Button>
            <Button level="primary" onClick={handleConfirm}>
              {translate('pim_common.confirm')}
            </Button>
          </Modal.BottomButtons>
        </Modal>
      )}
      <span>
        {translate('pim_connector.export.categories.selector.label', {count: filter.value.length}, filter.value.length)}
      </span>
      <Button level="secondary" onClick={openCategoriesModal}>
        {translate('pim_common.edit')}
      </Button>
    </Container>
  );
};

export {CategoryFilter};
export type {CategoryFilterType};
