import React, {useState, useEffect} from "react";
import useFetchCategoryTrees from "../../../../infrastructure/hooks/Dashboard/useFetchCategoryTrees";
import CategoryTreeNode from "./CategoryTreeNode";
import Modal from "../../Modal";

const __ = require('oro/translator');
const UserContext = require('pim/user-context');

interface CategoryModalProps {
  onSelectCategory: (categoryCode: string, categoryLabel: string, categoryId: string, rootCategoryId: string) => void;
  onConfirm: () => void;
  onDismissModal: () => void;
  isVisible: boolean;
  selectedCategories: string[];
  withCheckBox: boolean;
  subtitle: string;
  description: string;
  errorMessage: string | null;
}

const CategoryModal = ({onSelectCategory, onConfirm, onDismissModal, isVisible, selectedCategories, withCheckBox, subtitle, description, errorMessage}: CategoryModalProps) => {

  const [selectedCategoryTree, setSelectedCategoryTree] = useState<any | null>(null);

  const categoryTrees = useFetchCategoryTrees();

  useEffect(() => {
    if (Object.keys(categoryTrees).length > 0) {
      setSelectedCategoryTree(Object.values(categoryTrees)[0] as object);
    }
  }, [categoryTrees]);

  const uiLocale = UserContext.get('uiLocale');

  const getCategoryFromCode = (categoryCode: string): any => {
    return Object.values(categoryTrees).find((category: any) => category.code === categoryCode);
  };

  if (!isVisible) {
    return (<></>);
  }

  const modalContent = <>
    {null !== errorMessage && (
      <div className="AknMessageBox AknMessageBox--error AknMessageBox--withIcon">
        {errorMessage}
      </div>
    )}
    <div className="AknHorizontalNavtab nav nav-tabs">
      <div className="AknButtonList catalog-switcher">
        <ul className="AknHorizontalNavtab-list nav nav-tabs">
          {Object.values(categoryTrees).map((category: any) => {
            return (
              <li key={category.code} className={`AknHorizontalNavtab-item tree-selector ${selectedCategoryTree && selectedCategoryTree.code === category.code ? 'active' : ''}`} data-code={category.code} onClick={() => setSelectedCategoryTree(getCategoryFromCode(category.code))}>
                <div className="AknHorizontalNavtab-link">
                  <span className="tree-label label">{category.labels[uiLocale] ? category.labels[uiLocale] : '[' + category.code + ']'}</span>
                </div>
              </li>
            )
          })}
        </ul>
      </div>
    </div>

    <div className="AknTabContainer-content tab-content" style={{marginLeft: "-18px"}}>
      <div className="tree root-unselectable">
        <div className="buffer-small-left jstree jstree-0 jstree-focused jstree-default">
          <ul>
            {selectedCategoryTree !== null && (
              <CategoryTreeNode
                key={selectedCategoryTree.code}
                categoryId={'' + selectedCategoryTree.id}
                categoryLabel={selectedCategoryTree.labels[uiLocale]}
                categoryCode={selectedCategoryTree.code}
                locale={uiLocale}
                isOpened={true}
                onSelectCategory={onSelectCategory}
                hasChildren={true}
                selectedCategories={selectedCategories}
                withCheckbox={withCheckBox}
                rootCategoryId={selectedCategoryTree.id}
              />
            )}
          </ul>
        </div>
      </div>
    </div>
  </>;

  return (
    <Modal
      cssClass={'AknDataQualityInsightsCategoryModal'}
      title={__('akeneo_data_quality_insights.title')}
      subtitle={subtitle}
      description={description}
      illustrationLink={'bundles/pimui/images/illustrations/Product-categories.svg'}
      modalContent={modalContent}
      onConfirm={onConfirm}
      onDismissModal={onDismissModal}
      enableSaveButton={null === errorMessage}
    />
  );
};

export default CategoryModal;
