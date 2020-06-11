import React, { useState } from 'react';
import { AddToCategoryAction } from '../../../../models/actions';
import { ActionLineProps } from './ActionLineProps';
import { NetworkLifeCycle } from '../../../../components/CategoryTree/hooks/NetworkLifeCycle.types';
import { CategoryTreeModel } from '../../../../components/CategoryTree/category-tree.types';
import { Category } from "../../../../models";
import { getCategoriesByIdentifiers } from "../../../../repositories/CategoryRepository";
import { useBackboneRouter } from "../../../../dependenciesTools/hooks";
import { getCategoriesTrees } from "../../../../components/CategoryTree/category-tree.getters";

type Props = {
  action: AddToCategoryAction;
} & ActionLineProps;

const AddToCategoryActionLine: React.FC<Props> = ({
  lineNumber,
  action,
  currentCatalogLocale,
}) => {
  const router = useBackboneRouter();
  console.log('lineNumber', lineNumber);
  console.log('action', action);
  console.log('router', router);
  console.log('currentCatalogLocale', currentCatalogLocale);

  const [ categories, setCategories ] = React.useState<NetworkLifeCycle<Category[]>>({ status: 'PENDING' });
  const [ categoryTrees, setCategoriesTrees ] = useState<NetworkLifeCycle<CategoryTreeModel[]>>({ status: 'PENDING' });
  const [ currentCategoryTree, setCurrentCategoryTree ] = useState<CategoryTreeModel>();

  React.useEffect(() => {
    getCategoriesByIdentifiers(action.value || [], router).then((indexedCategories) => {
      const categories = Object.values(indexedCategories);
      const nonNullCategories = categories.filter(category => category !== null) as Category[];
      setCategories({ status: 'COMPLETE', data: nonNullCategories });
    });
    getCategoriesTrees(setCategoriesTrees);
  }, []);

  console.log(categories.data);
  console.log(categoryTrees.data);

  if (
    !categories.data || categories.status !== 'COMPLETE' ||
    !categoryTrees.data || categoryTrees.status !== 'COMPLETE'
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
  const categoryTreesWithSelectedCategoriesMap = new Map<CategoryTreeModel, Category[]>(categoryTreesWithSelectedCategories);

  return <>
    <div className="left">
      <ul>
        {Array.from(categoryTreesWithSelectedCategoriesMap.entries()).map((toto) => {
          const [categoryTree] = toto;
          return <li key={categoryTree.code}>
            {categoryTree.labels[currentCatalogLocale] || `[${categoryTree.code}]`}
          </li>
        })}
      </ul>
    </div>
    <div className="right">
      {currentCategoryTree && (
        <ul>
          {(categoryTreesWithSelectedCategoriesMap.get(currentCategoryTree) || []).map((category) => {
            return <li key={category.code}>{category.labels[currentCatalogLocale] || `[${category.code}]`}</li>
          })}
        </ul>
      )}
    </div>
  </>

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
