import React from 'react';
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
  categoriesSelected: string[];
  setCategoriesSelected: (categoriesSelected: string[]) => void;
};

const CategoryFilter = ({categoriesSelected, setCategoriesSelected}: CategoryFilterProps) => {
  const translate = useTranslate();
  const [isCategoriesModalOpen, openCategoriesModal, closeCategoriesModal] = useBooleanState();

  const handleConfirm = () => {
    closeCategoriesModal();
  };

  return (
    <Container>
      {isCategoriesModalOpen && (
        <Modal
          closeTitle={translate('pim_common.close')}
          onClose={closeCategoriesModal}
          illustration={<ProductCategoryIllustration />}
        >
          <Modal.Title>{translate('pim_connector.export.categories.selector.modal.title')}</Modal.Title>
          <MultiCategoryTreeSelector
            categoriesSelected={categoriesSelected}
            onCategorySelected={setCategoriesSelected}
          />
          <Modal.BottomButtons>
            <Button level="tertiary" onClick={closeCategoriesModal}>
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
          {count: categoriesSelected.length},
          categoriesSelected.length
        )}
      </span>
      <Button level="secondary" onClick={openCategoriesModal}>
        {translate('pim_common.edit')}
      </Button>
    </Container>
  );
};

export {CategoryFilter};
