import React, {FC, useEffect, useState} from 'react';
import {createPortal} from 'react-dom';
import CategoryModal from '../CategoryModal/CategoryModal';
import {useDashboardContext} from '../../../context/DashboardContext';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import styled from 'styled-components';

interface Props {
  categoryCode: string | null;
}

const Container = styled.div.attrs(() => ({
  className: 'AknDropdown AknButtonList-item',
}))`
  position: relative;
  margin-top: -2px;
`;
const Toggle = styled.button.attrs(() => ({
  className: 'AknActionButton AknActionButton--withoutBorder',
}))`
  color: ${({theme}) => theme.color.grey140};
  font-size: ${({theme}) => theme.fontSize.default};
  white-space: nowrap;
`;

const ToggleSelection = styled.span`
  color: ${({theme}) => theme.color.purple100};
  font-size: ${({theme}) => theme.fontSize.default};
  margin-left: 3px;
`;

const CategoryFilter: FC<Props> = ({categoryCode}) => {
  const [selectedCategoryCode, setSelectedCategoryCode] = useState<string | null>(null);
  const [selectedCategoryLabel, setSelectedCategoryLabel] = useState<string | null>(null);
  const [selectedCategoryId, setSelectedCategoryId] = useState<string | null>(null);
  const [selectedRootCategoryId, setSelectedRootCategoryId] = useState<string | null>(null);
  const [modalElement, setModalElement] = useState<HTMLDivElement | null>(null);
  const [showModal, setShowModal] = useState<boolean>(false);
  const {updateDashboardFilters} = useDashboardContext();
  const translate = useTranslate();

  useEffect(() => {
    setSelectedCategoryCode(categoryCode);
  }, [categoryCode]);

  useEffect(() => {
    const modal = document.createElement('div');
    setModalElement(modal);
    document.body.appendChild(modal);

    return () => {
      if (modalElement) {
        document.body.removeChild(modalElement);
      }
    };
  }, []);

  const onSelectCategory = (
    categoryCode: string,
    categoryLabel: string,
    categoryId: string,
    rootCategoryId: string
  ) => {
    setSelectedCategoryCode(categoryCode);
    setSelectedCategoryLabel(categoryLabel);
    setSelectedCategoryId(categoryId);
    setSelectedRootCategoryId(rootCategoryId);
  };

  const onValidate = () => {
    if (selectedCategoryCode !== null && selectedCategoryId && selectedRootCategoryId) {
      updateDashboardFilters(null, {
        code: selectedCategoryCode,
        id: selectedCategoryId,
        rootCategoryId: selectedRootCategoryId,
      });
    }
    setShowModal(false);
  };

  const onDismissModal = () => setShowModal(false);

  return (
    <>
      <Container>
        <Toggle onClick={() => setShowModal(true)} data-testid={'dqiCategoryFilter'}>
          {translate('pim_enrich.entity.category.uppercase_label')}:
          <ToggleSelection>
            {selectedCategoryCode && selectedCategoryLabel ? selectedCategoryLabel : translate('pim_common.all')}
          </ToggleSelection>
        </Toggle>
      </Container>

      {modalElement &&
        createPortal(
          <CategoryModal
            onSelectCategory={onSelectCategory}
            onConfirm={onValidate}
            onDismissModal={onDismissModal}
            isVisible={showModal}
            selectedCategories={selectedCategoryCode === null ? [] : [selectedCategoryCode]}
            withCheckBox={false}
            subtitle={translate('akeneo_data_quality_insights.dqi_dashboard.category_modal_filter.subtitle')}
            description={translate('akeneo_data_quality_insights.dqi_dashboard.category_modal_filter.message')}
            errorMessage={null}
          />,
          modalElement
        )}
    </>
  );
};

export {CategoryFilter};
