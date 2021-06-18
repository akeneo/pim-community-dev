import React, {useState} from 'react';
import styled from 'styled-components';
import {Button, Modal, ProductCategoryIllustration, useBooleanState} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {MultiCategoryTreeSelector} from './MultiCategoryTreeSelector';

const Container = styled.div`
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
`;

type CategoryFilterProps = {
  initialCategorySelection: string[];
  onCategorySelection: (updatedCategorySelection: string[]) => void;
};

const CategoryFilter = ({initialCategorySelection, onCategorySelection}: CategoryFilterProps) => {
  const translate = useTranslate();
  const [isCategoriesModalOpen, openCategoriesModal, closeCategoriesModal] = useBooleanState();
  const [selectedCategories, setSelectedCategories] = useState(initialCategorySelection);
  const handleConfirm = () => {
    onCategorySelection(selectedCategories);
    closeCategoriesModal();
  };
  const handleClose = () => {
    setSelectedCategories(initialCategorySelection);
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
          <MultiCategoryTreeSelector
            categorySelection={selectedCategories}
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
        {translate(
          'pim_connector.export.categories.selector.label',
          {count: initialCategorySelection.length},
          initialCategorySelection.length
        )}
      </span>
      <Button level="secondary" onClick={openCategoriesModal}>
        {translate('pim_common.edit')}
      </Button>
    </Container>
  );
};

export {CategoryFilter};
