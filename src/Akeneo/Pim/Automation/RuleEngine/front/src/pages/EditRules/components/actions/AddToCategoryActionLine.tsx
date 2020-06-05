import React, { useState, useEffect } from 'react';
import { ActionTemplate } from './ActionTemplate';
import { AddToCategoryAction } from '../../../../models/actions';
import { ActionLineProps } from './ActionLineProps';
import { getCategoriesTrees } from '../../../../components/CategoryTree/category-tree.getters';
import { NetworkLifeCycle } from '../../../../components/CategoryTree/hooks/NetworkLifeCycle.types';
import { CategoryTreeModel } from '../../../../components/CategoryTree/category-tree.types';
import { Select2SimpleSyncWrapper } from '../../../../components/Select2Wrapper';
import { AkeneoSpinner } from '../../../../components/AkeneoSpinner';
import { AddToCategoryTree } from './AddToCategoryTree';

type Props = {
  action: AddToCategoryAction;
} & ActionLineProps;

const AddToCategoryActionLine: React.FC<Props> = ({
  translate,
  lineNumber,
  action,
  handleDelete,
  router,
  currentCatalogLocale,
}) => {
  console.log('lineNumber', lineNumber);
  console.log('action', action);
  console.log('router', router);
  console.log('currentCatalogLocale', currentCatalogLocale);

  const [categoriesTrees, setCategoriesTrees] = useState<
    NetworkLifeCycle<CategoryTreeModel[]>
  >({
    status: 'PENDING',
    data: [],
  });

  const [categoryTreesSelected, setCategoryTreesSelected] = useState<
    Map<number, string[]>
  >(new Map());

  useEffect(() => {
    const initAddToCategoryData = async () => {
      await getCategoriesTrees(setCategoriesTrees);
    };
    initAddToCategoryData();
  }, []);

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
                  categoryTree={categoriesTrees.data?.find(
                    c => c.id === categoryTreeId
                  )}
                  onClickCategory={handleClickCategory}
                  selectedCategoriesLength={selectedCategories.length}
                />
              );
            }
          )}
          {categoriesTrees.status === 'PENDING' ? (
            // Replace with a proper select loader component
            <AkeneoSpinner />
          ) : (
            <Select2SimpleSyncWrapper
              id='add_category_select_tree'
              label='Category tree (required)'
              hiddenLabel
              onSelecting={handleAddCategoryTree}
              data={
                categoriesTrees.data?.map(categoryTree => ({
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
};

export { AddToCategoryActionLine };
