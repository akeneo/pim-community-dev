import React, {useState, useEffect} from "react";
import useFetchCategoryTrees from "../../../../../infrastructure/hooks/Dashboard/useFetchCategoryTrees";
import CategoryTreeNode from "./CategoryTreeNode";

const __ = require('oro/translator');
const UserContext = require('pim/user-context');

interface CategoryModalProps {
  onSelectCategory: (categoryCode: string, categoryLabel: string) => void;
  onValidate: () => void;
  onDismissModal: () => void;
  isVisible: boolean;
  selectedCategory: string | null;
}

const CategoryModal = ({onSelectCategory, onValidate, onDismissModal, isVisible, selectedCategory}: CategoryModalProps) => {

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

  return (
    <div className="modal in AknDataQualityInsightsCategoryFilter">
      <div className="AknFullPage">
        <div className="AknFullPage-content AknFullPage-content--withIllustration">
          <div>
            <img src="bundles/pimui/images/illustrations/Product-categories.svg"/>
          </div>
          <div>
            <div className="AknFullPage-titleContainer">
              <div className="AknFullPage-subTitle">{__('akeneo_data_quality_insights.title')}</div>
              <div className="AknFullPage-title">{__('akeneo_data_quality_insights.dqi_dashboard.category_modal.subtitle')}</div>
              <div className="AknFullPage-description">{__('akeneo_data_quality_insights.dqi_dashboard.category_modal.message')}</div>
            </div>
            <div className="modal-body">

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
                          selectedCategory={selectedCategory}
                        />
                      )}
                    </ul>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>

      <div className="AknFullPage-cancel cancel" onClick={() => onDismissModal()}/>
      <span className="AknButton AknFullPage-ok AknButton--apply" onClick={() => onValidate()}>
        {__('pim_common.save')}
      </span>

    </div>
  );
};

export default CategoryModal;
