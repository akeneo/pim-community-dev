import React, { useState } from 'react';
import { AddToCategoryAction } from '../../../../models/actions';
import { ActionLineProps } from './ActionLineProps';
import { NetworkLifeCycle } from '../../../../components/CategoryTree/hooks/NetworkLifeCycle.types';
import { CategoryTreeModel } from '../../../../components/CategoryTree/category-tree.types';
import { Category, CategoryCode, CategoryId } from "../../../../models";
import { getCategoriesByIdentifiers, getCategoryByIdentifier } from "../../../../repositories/CategoryRepository";
import { useBackboneRouter } from "../../../../dependenciesTools/hooks";
import { getCategoriesTrees } from "../../../../components/CategoryTree/category-tree.getters";
import { ActionTemplate } from "./ActionTemplate";
import { ActionGrid, ActionLeftSide, ActionRightSide, ActionTitle } from "./ActionLine";
import { LineErrors } from "../LineErrors";
import styled from 'styled-components';
import { Select2Wrapper } from "../../../../components/Select2Wrapper";
import { CategorySelector } from "../../../../components/Selectors/CategorySelector";

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
  const router = useBackboneRouter();
  console.log('lineNumber', lineNumber);
  console.log('action', action);
  console.log('router', router);
  console.log('currentCatalogLocale', currentCatalogLocale);

  const [categories, setCategories] = React.useState<NetworkLifeCycle<Category[]>>({ status: 'PENDING' });
  const [categoryTrees, setCategoriesTrees] = useState<NetworkLifeCycle<CategoryTreeModel[]>>({ status: 'PENDING' });
  const [currentCategoryTree, setCurrentCategoryTree] = useState<CategoryTreeModel>();
  const [closeTick, setCloseTick] = React.useState<boolean>(false);
  const [categoryTreesWithSelectedCategoriesMap, setCategoryTreesWithSelectedCategoriesMap] = React.useState<Map<CategoryTreeModel, Category[]>>();

  const initializeToto = () => {
    const categoryTreesWithSelectedCategories = (categoryTrees.data || []).reduce((previousValue, categoryTree) => {
      const categoriesData: Category[] = (categories.data || []);
      const matchingCategories = categoriesData.filter(category => category.root === categoryTree.id);
      if (matchingCategories.length) {
        previousValue.push([categoryTree, matchingCategories]);
      }
      return previousValue;
    }, [] as any[]);
    if (currentCategoryTree === undefined) {
      setCurrentCategoryTree(categoryTreesWithSelectedCategories[0][0]);
    }
    return new Map<CategoryTreeModel, Category[]>(categoryTreesWithSelectedCategories);
  }

  React.useEffect(() => {
    getCategoriesByIdentifiers(action.value || [], router).then((indexedCategories) => {
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
      setCategoryTreesWithSelectedCategoriesMap(initializeToto());
    }
  }, [categories, categoryTrees]);

  if (
    !categories.data || categories.status !== 'COMPLETE' ||
    !categoryTrees.data || categoryTrees.status !== 'COMPLETE' ||
    !categoryTreesWithSelectedCategoriesMap
  ) {
    return <div>Loading</div>
  }

  const categoryTreesWithSelectedCategories = categoryTrees.data.reduce((previousValue, categoryTree) => {
    const categoriesData: Category[] = (categories.data || []);
    const matchingCategories = categoriesData.filter(category => category.root === categoryTree.id);
    if (matchingCategories.length) {
      previousValue.push([categoryTree, matchingCategories]);
    }
    return previousValue;
  }, [] as any[]);
  if (currentCategoryTree === undefined) {
    setCurrentCategoryTree(categoryTreesWithSelectedCategories[0][0]);
  }

  const handleAddCategoryTree = (categoryTreeId: CategoryId) => {
    if (!categoryTreesWithSelectedCategoriesMap) {
      return;
    }
    categoryTreesWithSelectedCategoriesMap.set(
      (categoryTrees.data || []).find(categoryTree => categoryTree.id === categoryTreeId) as CategoryTreeModel,
      []
    );
    setCategoryTreesWithSelectedCategoriesMap(new Map(categoryTreesWithSelectedCategoriesMap));
  }

  const getNonSelectedCategoryTrees = () => {
    const nonSelectedcategoryTrees = (categoryTrees.data || []).filter((categoryTree) => {
      return !Array.from(categoryTreesWithSelectedCategoriesMap.entries()).some((toto) => {
        const [ categoryTreeLeft ] = toto;
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
    });
    console.log(categoryCode);
  }

  return (
    <ActionTemplate
      title='Set Action'
      helper='This feature is under development. Please use the import to manage your rules.'
      legend='This feature is under development. Please use the import to manage your rules.'
      handleDelete={handleDelete}>
      <ActionGrid>
        <ActionLeftSide>
          <div className='AknFormContainer'>
            <ActionTitle>
              Select your category trees
            </ActionTitle>
            <label className="AknFieldContainer-label">Category tree (required)</label>
            <ul>
              {Array.from(categoryTreesWithSelectedCategoriesMap.entries()).map((toto) => {
                const [categoryTree] = toto;
                return <li key={categoryTree.code}>
                  <CategoryTreeButton
                    className='AknTextField'
                    onClick={(e) => { e.preventDefault(); setCurrentCategoryTree(categoryTree) }}
                  >
                    {categoryTree.labels[currentCatalogLocale] || `[${categoryTree.code}]`}
                    <CategoryTreeHelper>
                      {(categoryTreesWithSelectedCategoriesMap.get(categoryTree) || []).length} selected categories
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
                placeholder={'Select your category tree'}
                data={getNonSelectedCategoryTrees()}
                hiddenLabel={true}
              />}
            </ul>
          </div>
        </ActionLeftSide>
        <ActionRightSide>
          <div className='AknFormContainer'>
            <ActionTitle>
              Select your categories
            </ActionTitle>
            {currentCategoryTree && (
              <>
                <label className="AknFieldContainer-label">Categories (required)</label>
                <ul>
                  {(categoryTreesWithSelectedCategoriesMap.get(currentCategoryTree) || []).map((category, i) => {
                    return (
                      <li key={category.code}>
                        <CategorySelector
                          locale={currentCatalogLocale}
                          onDelete={() => {}}
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
            )}
          </div>
        </ActionRightSide>
      </ActionGrid>
      <LineErrors lineNumber={lineNumber} type='actions'/>
    </ActionTemplate>
  );
};

export { AddToCategoryActionLine };
