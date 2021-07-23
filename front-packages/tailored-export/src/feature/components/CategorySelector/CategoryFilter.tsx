import React, {useState} from 'react';
import styled from 'styled-components';
import {BooleanInput, Button, Field, Modal, ProductCategoryIllustration, useBooleanState} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {MultiCategoryTreeSelector} from './MultiCategoryTreeSelector';
import {useCategoryTrees} from '../../hooks';

const Container = styled.div`
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
`;
type Operator = 'IN CHILDREN' | 'IN' | 'NOT IN';
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
  const [shouldIncludeSubCategories, setShouldIncludeSubCategories] = useState<boolean>(operator === 'IN CHILDREN');

  const categoryTrees = useCategoryTrees(categorySelection, shouldIncludeSubCategories);
  const totalCategorySelected = categoryTrees.reduce((carry, categoryTree) => {
    return carry + Number(categoryTree.selectedCategoryCount);
  }, 0);

  const handleShouldIncludeSubCategoryChange = (shouldIncludeSubCategory: boolean) => {
    setShouldIncludeSubCategories(shouldIncludeSubCategory);
  };
  const handleConfirm = () => {
    const updatedFilter: CategoryFilterType =
      0 === categorySelection.length
        ? {...filter, operator: 'NOT IN', value: []}
        : {
            ...filter,
            operator: shouldIncludeSubCategories ? 'IN CHILDREN' : 'IN',
            value: categorySelection,
          };
    onChange(updatedFilter);
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
              value={shouldIncludeSubCategories}
              readOnly={false}
              onChange={handleShouldIncludeSubCategoryChange}
            />
          </Field>
          <MultiCategoryTreeSelector
            categorySelection={categorySelection}
            onCategorySelection={setSelectedCategories}
            shouldIncludeSubCategories={shouldIncludeSubCategories}
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
        {translate(
          'pim_connector.export.categories.selector.label',
          {count: totalCategorySelected},
          totalCategorySelected
        )}
      </span>
      <Button level="secondary" onClick={openCategoriesModal}>
        {translate('pim_common.edit')}
      </Button>
    </Container>
  );
};

export {CategoryFilter};
export type {CategoryFilterType};
