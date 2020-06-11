import React, { useState } from 'react';
import { AddToCategoryAction } from '../../../../models/actions';
import { ActionLineProps } from './ActionLineProps';
import { NetworkLifeCycle } from '../../../../components/CategoryTree/hooks/NetworkLifeCycle.types';
import { CategoryTreeModel } from '../../../../components/CategoryTree/category-tree.types';
import { Category, CategoryCode, CategoryId } from "../../../../models";
import { getCategoriesByIdentifiers, getCategoryByIdentifier } from "../../../../repositories/CategoryRepository";
import { useBackboneRouter, useTranslate } from "../../../../dependenciesTools/hooks";
import { getCategoriesTrees } from "../../../../components/CategoryTree/category-tree.getters";
import { ActionTemplate } from "./ActionTemplate";
import { ActionGrid, ActionLeftSide, ActionRightSide, ActionTitle } from "./ActionLine";
import { LineErrors } from "../LineErrors";
import styled from 'styled-components';
import { Select2Wrapper } from "../../../../components/Select2Wrapper";
import { CategorySelector } from "../../../../components/Selectors/CategorySelector";
import { useFormContext } from 'react-hook-form';
import { useRegisterConst } from "../../hooks/useRegisterConst";

const CategoryTreeHelper = styled.span`
  text-transform: uppercase;
  position: absolute;
  right: 10px;
  border: 1px solid #a1a9b7;
  font-size: 11px;
  height: 18px;
  line-height: 18px;
  border-radius: 2px;
  top: 10px;
  padding: 0 3px;
`;

const CategoryTreeButton = styled.button`
  margin-bottom: 5px;
  position: relative;
  background: white;
  text-align: left;
  cursor: pointer;
`

type Props = {
  action: AddToCategoryAction;
} & ActionLineProps;

const AddToCategoryActionLine: React.FC<Props> = ({
  lineNumber,
  action,
  currentCatalogLocale,
  handleDelete,
}) => {
  // TODO handle unknown categories

  const router = useBackboneRouter();
  const translate = useTranslate();
  const { setValue, register } = useFormContext();

  useRegisterConst(`content.actions[${lineNumber}].type`, 'add');
  useRegisterConst(`content.actions[${lineNumber}].field`, 'categories');

  const [categories, setCategories] = React.useState<NetworkLifeCycle<Category[]>>({ status: 'PENDING' });
  const [categoryTrees, setCategoriesTrees] = useState<NetworkLifeCycle<CategoryTreeModel[]>>({ status: 'PENDING' });
  const [currentCategoryTree, setCurrentCategoryTree] = useState<CategoryTreeModel>();
  const [closeTick, setCloseTick] = React.useState<boolean>(false);
  const [categoryTreesWithSelectedCategoriesMap, setCategoryTreesWithSelectedCategoriesMap] = React.useState<Map<CategoryTreeModel, Category[]>>();

  /**
   * Initialize the main object for this component. This object is a Map, having
   * - a CategoryTreeModel as key
   * - the selected categories as value
   */
  const initializeCategoryTreesWithSelectedCategories = () => {
    const categoryTreesWithSelectedCategories = (categoryTrees.data || []).reduce((previousValue, categoryTree) => {
      const categoriesData: Category[] = (categories.data || []);
      const matchingCategories = categoriesData.filter(category => category.root === categoryTree.id);
      if (matchingCategories.length) {
        previousValue.push([categoryTree, matchingCategories]);
      }
      return previousValue;
    }, [] as any[]);
    if (currentCategoryTree === undefined && categoryTreesWithSelectedCategories.length) {
      setCurrentCategoryTree(categoryTreesWithSelectedCategories[0][0]);
    }
    return new Map<CategoryTreeModel, Category[]>(categoryTreesWithSelectedCategories);
  }

  React.useEffect(() => {
    register(`content.actions[${lineNumber}].value`);
    setValue(`content.actions[${lineNumber}].value`, action.value);
  }, [lineNumber]);

  React.useEffect(() => {
    getCategoriesByIdentifiers((action.value || []), router).then((indexedCategories) => {
      const categories = Object.values(indexedCategories);
      const nonNullCategories = categories.filter(category => category !== null) as Category[];
      setCategories({ status: 'COMPLETE', data: nonNullCategories });
    });
    getCategoriesTrees(setCategoriesTrees);
  }, []);

  React.useEffect(() => {
    if (
      categoryTreesWithSelectedCategoriesMap === undefined &&
      categories.data && categories.status === 'COMPLETE' &&
      categoryTrees.data && categoryTrees.status === 'COMPLETE'
    ) {
      setCategoryTreesWithSelectedCategoriesMap(initializeCategoryTreesWithSelectedCategories());
    }
  }, [categories, categoryTrees]);

  if (
    !categories.data || categories.status !== 'COMPLETE' ||
    !categoryTrees.data || categoryTrees.status !== 'COMPLETE' ||
    !categoryTreesWithSelectedCategoriesMap
  ) {
    return <img
      src='/bundles/pimui/images//loader-V2.svg'
      alt={translate('pim_common.loading')}
    />;
  }

  const handleAddCategoryTree = (categoryTreeId: CategoryId) => {
    if (!categoryTreesWithSelectedCategoriesMap) {
      return;
    }
    const categoryTree = (categoryTrees.data || []).find(categoryTree => categoryTree.id === categoryTreeId) as CategoryTreeModel;
    categoryTreesWithSelectedCategoriesMap.set(categoryTree, []);
    setCategoryTreesWithSelectedCategoriesMap(new Map(categoryTreesWithSelectedCategoriesMap));
    setCurrentCategoryTree(categoryTree);
  }

  const getNonSelectedCategoryTrees = () => {
    const nonSelectedcategoryTrees = (categoryTrees.data || []).filter((categoryTree) => {
      return !Array.from(categoryTreesWithSelectedCategoriesMap.entries()).some(([ categoryTreeLeft, _categories ]) => {
        return categoryTreeLeft.id === categoryTree.id;
      });
    })
    return nonSelectedcategoryTrees.map((categoryTree) => {
      return {
        id: categoryTree.id,
        text: categoryTree.labels[currentCatalogLocale] || `[${categoryTree.code}]`
      }
    })
  }

  const getSelectedCategories: () => CategoryCode[] = () => {
    return Array.from(categoryTreesWithSelectedCategoriesMap.entries()).reduce((previousValue, [ _tree, categories ]) => {
      return [...previousValue, ...categories.map(category => category.code)];
    }, [] as CategoryCode[]);
  }

  const handleCategorySelect = (categoryCode: CategoryCode, categoryTree: CategoryTreeModel, index?: number) => {
    getCategoryByIdentifier(categoryCode, router).then((category) => {
      if (category === null) {
        throw new Error('Category not found');
      }
      const previousCategories = categoryTreesWithSelectedCategoriesMap.get(categoryTree) || [];
      if (index) {
        previousCategories[index] = category;
      } else {
        previousCategories.push(category);
      }
      categoryTreesWithSelectedCategoriesMap.set(categoryTree, previousCategories);
      setCategoryTreesWithSelectedCategoriesMap(new Map(categoryTreesWithSelectedCategoriesMap));
      setValue(`content.actions[${lineNumber}].value`, getSelectedCategories());
    });
  }

  const handleCategoryDelete = (categoryTree: CategoryTreeModel, index: number) => {
    const previousCategories = categoryTreesWithSelectedCategoriesMap.get(categoryTree) || [];
    previousCategories.splice(index, 1);
    setCategoryTreesWithSelectedCategoriesMap(new Map(categoryTreesWithSelectedCategoriesMap));
    setValue(`content.actions[${lineNumber}].value`, getSelectedCategories());
  }

  return (
    <ActionTemplate
      title={translate('pimee_catalog_rule.form.edit.actions.add_category.title')}
      helper={translate('pimee_catalog_rule.form.edit.actions.add_category.helper')}
      legend={translate('pimee_catalog_rule.form.edit.actions.add_category.helper')}
      handleDelete={handleDelete}>
      <ActionGrid>
        <ActionLeftSide>
          <div className='AknFormContainer'>
            <ActionTitle>
              {translate('pimee_catalog_rule.form.edit.actions.add_category.select_category_trees')}
            </ActionTitle>
            <label className="AknFieldContainer-label">
              {`${translate('pimee_catalog_rule.form.edit.actions.add_category.category_tree')} ${translate('pim_common.required_label')}`}
            </label>
            <ul>
              {Array.from(categoryTreesWithSelectedCategoriesMap.entries()).map(([categoryTree, _categories]) => {
                return <li key={categoryTree.code}>
                  {/* TODO add Selected state */}
                  <CategoryTreeButton
                    className='AknTextField'
                    onClick={(e) => { e.preventDefault(); setCurrentCategoryTree(categoryTree) }}
                  >
                    {categoryTree.labels[currentCatalogLocale] || `[${categoryTree.code}]`}
                    <CategoryTreeHelper>
                      {translate(
                        'pimee_catalog_rule.form.edit.actions.add_category.categories_selected',
                        { count: (categoryTreesWithSelectedCategoriesMap.get(categoryTree) || []).length },
                        (categoryTreesWithSelectedCategoriesMap.get(categoryTree) || []).length
                      )}
                    </CategoryTreeHelper>
                  </CategoryTreeButton>
                </li>
              })}
              {(getNonSelectedCategoryTrees().length > 0) && <Select2Wrapper
                multiple={false}
                label={'add-category-tree'}
                onSelecting={(event: any) => {
                  event.preventDefault();
                  setCloseTick(!closeTick);
                  handleAddCategoryTree(event.val);
                }}
                placeholder={translate('pimee_catalog_rule.form.edit.actions.add_category.select_category_tree')}
                data={getNonSelectedCategoryTrees()}
                hiddenLabel={true}
              />}
            </ul>
          </div>
        </ActionLeftSide>
        <ActionRightSide>
          <div className='AknFormContainer'>
            <ActionTitle>
              {translate('pimee_catalog_rule.form.edit.actions.add_category.select_categories')}
            </ActionTitle>
            {currentCategoryTree ? (
              <>
                <label className="AknFieldContainer-label">
                  {`${translate('pim_enrich.entity.category.plural_label')} ${translate('pim_common.required_label')}`}
                </label>
                <ul>
                  {(categoryTreesWithSelectedCategoriesMap.get(currentCategoryTree) || []).map((category, i) => {
                    return (
                      <li key={category.code}>
                        <CategorySelector
                          locale={currentCatalogLocale}
                          onDelete={() => handleCategoryDelete(currentCategoryTree, i)}
                          onSelectCategory={categoryCode => handleCategorySelect(categoryCode, currentCategoryTree, i)}
                          selectedCategory={category}
                          categoryTreeSelected={currentCategoryTree}
                        />
                      </li>
                    )
                  })}
                </ul>
                <CategorySelector
                locale={currentCatalogLocale}
                onDelete={() => {}}
                onSelectCategory={categoryCode => handleCategorySelect(categoryCode, currentCategoryTree)}
                categoryTreeSelected={currentCategoryTree}
                />
              </>
            ) : (
              <div>{translate('pimee_catalog_rule.form.edit.actions.add_category.no_category_tree')}</div>
            )}
          </div>
        </ActionRightSide>
      </ActionGrid>
      <LineErrors lineNumber={lineNumber} type='actions'/>
    </ActionTemplate>
  );
};

export { AddToCategoryActionLine };
