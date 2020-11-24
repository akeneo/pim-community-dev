import React, {FC, useEffect, useState} from 'react';
import {createPortal} from 'react-dom';
import CategoryModal from '../../CategoryModal/CategoryModal';
import {DATA_QUALITY_INSIGHTS_DASHBOARD_FILTER_CATEGORY} from '../../../../constant';
import {useDashboardContext} from '../../../../context/DashboardContext';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

interface Props {
  categoryCode: string | null;
}

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
      window.dispatchEvent(
        new CustomEvent(DATA_QUALITY_INSIGHTS_DASHBOARD_FILTER_CATEGORY, {
          detail: {
            categoryCode: selectedCategoryCode,
          },
        })
      );
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
      <div className="AknFilterBox-filterContainer">
        <div className="AknFilterBox-filter" onClick={() => setShowModal(true)} data-testid={'dqiCategoryFilter'}>
          <span className="AknFilterBox-filterLabel">{translate('pim_enrich.entity.category.uppercase_label')}</span>
          <button type="button" className="AknFilterBox-filterCriteria ui-multiselect">
            <span>
              {selectedCategoryCode && selectedCategoryLabel ? selectedCategoryLabel : translate('pim_common.all')}
            </span>
          </button>
        </div>
      </div>

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
