import React, {FunctionComponent, useEffect, useState} from "react";
import {createPortal} from "react-dom";
import CategoryModal from "./CategoryModal";
import {DATA_QUALITY_INSIGHTS_DASHBOARD_FILTER_CATEGORY} from "../../../../listener";

const __ = require('oro/translator');

interface CategoryFilterProps {
  categoryCode: string | null;
}

const CategoryFilter: FunctionComponent<CategoryFilterProps> = ({categoryCode}) => {

  const [selectedCategoryCode, setSelectedCategoryCode] = useState<string | null>(null);
  const [selectedCategoryLabel, setSelectedCategoryLabel] = useState<string | null>(null);
  const [modalElement, setModalElement] = useState<HTMLDivElement|null>(null);
  const [showModal, setShowModal] = useState<boolean>(false);

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
    }
  }, []);

  const onSelectCategory = (categoryCode: string, categoryLabel: string) => {
    setSelectedCategoryCode(categoryCode);
    setSelectedCategoryLabel(categoryLabel);
  };

  const onValidate = () => {
    if(selectedCategoryCode !== null) {
      window.dispatchEvent(new CustomEvent(DATA_QUALITY_INSIGHTS_DASHBOARD_FILTER_CATEGORY, {detail: {
        categoryCode: selectedCategoryCode
      }}));
    }
    setShowModal(false)
  };

  const onDismissModal = () => setShowModal(false);

  return (
    <>

      <div className="AknFilterBox-filterContainer">
        <div className="AknFilterBox-filter" onClick={() => setShowModal(true)}>
          <span className="AknFilterBox-filterLabel">{__('pim_enrich.entity.category.uppercase_label')}</span>
          <button type="button" className="AknFilterBox-filterCriteria ui-multiselect">
            <span>{selectedCategoryCode && selectedCategoryLabel ? selectedCategoryLabel : __('pim_common.all')}</span>
          </button>
        </div>
      </div>

      {modalElement && createPortal(
        <CategoryModal
          onSelectCategory={onSelectCategory}
          onValidate={onValidate}
          onDismissModal={onDismissModal}
          isVisible={showModal}
          selectedCategory={selectedCategoryCode}
        />,
        modalElement
      )}

    </>
  )
};

export default CategoryFilter;
