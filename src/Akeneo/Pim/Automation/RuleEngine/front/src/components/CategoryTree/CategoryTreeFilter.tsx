import React from 'react';
import styled from 'styled-components';
import {AkeneoSpinner} from '../AkeneoSpinner';
import {SelectCategoriesTrees} from './components/SelectCategoriesTrees';
import {CategoryTree} from './components/CategoryTree';
import {
  CategoryTreeModel,
  CategoryTreeModelWithOpenBranch,
} from './category-tree.types';
import {Category, CategoryCode, LocaleCode} from '../../models';
import {NetworkLifeCycle} from './hooks/NetworkLifeCycle.types';

const ContainerCategoryTree = styled.div`
  margin: 0 20px;
`;

type Props = {
  categoryTrees: NetworkLifeCycle<CategoryTreeModel[]>;
  categoryTreeSelected?: CategoryTreeModel;
  locale: LocaleCode;
  onSelectCategory: (categoryCode: CategoryCode) => void;
  initCategoryTreeOpenBranch: NetworkLifeCycle<
    CategoryTreeModelWithOpenBranch[]
  >;
  selectedCategories: Category[];
  setCategoryTreeSelected: (category: CategoryTreeModel) => void;
};

const CategoryTreeFilter: React.FC<Props> = ({
  locale,
  onSelectCategory,
  selectedCategories,
  initCategoryTreeOpenBranch,
  categoryTrees,
  categoryTreeSelected,
  setCategoryTreeSelected,
}) => {
  if (
    categoryTrees.status === 'PENDING' ||
    !categoryTrees.data ||
    !categoryTreeSelected
  ) {
    return <AkeneoSpinner />;
  }
  return (
    <>
      <SelectCategoriesTrees
        currentCategoryTreeSelected={categoryTreeSelected}
        categoryTrees={categoryTrees.data}
        locale={locale}
        onClick={setCategoryTreeSelected}
      />
      <ContainerCategoryTree>
        {initCategoryTreeOpenBranch.status === 'PENDING' ||
        !initCategoryTreeOpenBranch.data ? (
          <AkeneoSpinner />
        ) : (
          <CategoryTree
            initCategoryTreeOpenBranch={initCategoryTreeOpenBranch.data[0]}
            categoryTree={categoryTreeSelected}
            locale={locale}
            onSelectCategory={onSelectCategory}
            selectedCategories={selectedCategories}
          />
        )}
      </ContainerCategoryTree>
    </>
  );
};

export {CategoryTreeFilter};
