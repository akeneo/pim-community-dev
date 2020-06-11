import React, { useState } from 'react';
import { AddToCategoryAction } from '../../../../models/actions';
import { ActionLineProps } from './ActionLineProps';
import { NetworkLifeCycle } from '../../../../components/CategoryTree/hooks/NetworkLifeCycle.types';
import { CategoryTreeModel } from '../../../../components/CategoryTree/category-tree.types';
import { Category, CategoryId } from "../../../../models";
import { getCategoriesByIdentifiers } from "../../../../repositories/CategoryRepository";
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
            <label className="AknFieldContainer-label">Categories (required)</label>
            {currentCategoryTree && (
              <ul>
                {(categoryTreesWithSelectedCategoriesMap.get(currentCategoryTree) || []).map((category) => {
                  return (
                    <li key={category.code}>
                      <CategorySelector
                        locale={currentCatalogLocale}
                        onDelete={() => {}}
                        onSelectCategory={() => {}}
                        selectedCategory={category}
                        categoryTreeSelected={currentCategoryTree}
                      />
                    </li>
                  )
                })}
              </ul>
            )}
            --Select category--
          </div>
        </ActionRightSide>
      </ActionGrid>
      <LineErrors lineNumber={lineNumber} type='actions'/>
    </ActionTemplate>
  );

  /*
  const handleAddCategoryTree = (event: any) => {
    const tmpCategoryTreesSelected = new Map(categoryTreesSelected);
    const key = Number(event.val);
    if (!tmpCategoryTreesSelected.has(key)) {
      tmpCategoryTreesSelected.set(key, []);
    }
    setCategoryTreesSelected(tmpCategoryTreesSelected);
  };

  const handleClickCategory = (categoryId: number, value: string) => {
    const tmpCategoryTreesSelected = new Map(categoryTreesSelected);
    const values = tmpCategoryTreesSelected.get(categoryId);
    if (values) {
      tmpCategoryTreesSelected.set(categoryId, [...values, value]);
    } else {
      tmpCategoryTreesSelected.set(categoryId, [value]);
    }
    setCategoryTreesSelected(tmpCategoryTreesSelected);
  };

  console.log({ categoryTreesSelected });

  return (
    <ActionTemplate
      translate={translate}
      title={translate('pimee_catalog_rule.form.edit.add_to_category')}
      helper={translate('pimee_catalog_rule.form.helper.add_to_category')}
      legend={translate('pimee_catalog_rule.form.legend.add_to_category')}
      handleDelete={handleDelete}>
      <div
        style={{
          display: 'flex',
          justifyContent: 'space-between',
          padding: '5px',
        }}>
        <fieldset style={{ width: '50%' }}>
          <legend
            style={{ color: '#9452BA', fontSize: '15px', padding: '10px 0' }}>
            Select your category trees
          </legend>
          <label htmlFor='add_category_select_tree'>
            Category tree (required)
          </label>
          {Array.from(categoryTreesSelected.entries()).map(
            ([categoryTreeId, selectedCategories]) => {
              return (
                <AddToCategoryTree
                  key={`${categoryTreeId}-category-tree`}
                  categoryTree={categoryTrees.data?.find(
                    c => c.id === categoryTreeId
                  )}
                  onClickCategory={handleClickCategory}
                  selectedCategoriesLength={selectedCategories.length}
                />
              );
            }
          )}
          {categoryTrees.status === 'PENDING' ? (
            // Replace with a proper select loader component
            <AkeneoSpinner />
          ) : (
            <Select2SimpleSyncWrapper
              id='add_category_select_tree'
              label='Category tree (required)'
              hiddenLabel
              onSelecting={handleAddCategoryTree}
              data={
                categoryTrees.data?.map(categoryTree => ({
                  id: categoryTree.id,
                  text: categoryTree.code,
                })) || []
              }
              placeholder='Select you category tree'
            />
          )}
        </fieldset>
        <fieldset style={{ width: '50%' }}>
          <legend
            style={{ color: '#9452BA', fontSize: '15px', padding: '10px 0' }}>
            Select your categories for master catalog
          </legend>
          <label>Categories (required)</label>
        </fieldset>
      </div>
    </ActionTemplate>
  );
   */
};

export { AddToCategoryActionLine };
