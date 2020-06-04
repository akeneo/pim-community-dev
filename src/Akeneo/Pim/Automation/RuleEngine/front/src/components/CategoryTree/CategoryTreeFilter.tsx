import React from 'react';
import styled from 'styled-components';
import { AkeneoSpinner } from '../AkeneoSpinner';
import { SelectCategoriesTrees } from './components/SelectCategoriesTrees';
import { CategoryTree } from './components/CategoryTree';
import {
  CategoryTreeModel,
  CategoryTreeModelWithOpenBranch,
} from './category-tree.types';
import { Category, CategoryCode, LocaleCode } from '../../models';
import { NetworkLifeCycle } from './hooks/NetworkLifeCycle.types';

const ContainerCategoryTree = styled.div`
  margin: 0 20px;
  border-top: ${({ theme }) => `1px solid ${theme.color.purple100}`};
`;

type Props = {
  categoriesTrees: NetworkLifeCycle<CategoryTreeModel[]>;
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
  categoriesTrees,
  categoryTreeSelected,
  setCategoryTreeSelected,
}) => {
  if (
    categoriesTrees.status === 'PENDING' ||
    !categoriesTrees.data ||
    !categoryTreeSelected
  ) {
    return <AkeneoSpinner />;
  }
  return (
    <>
      <SelectCategoriesTrees
        currentCategoryTreeSelected={categoryTreeSelected}
        categoriesTrees={categoriesTrees.data}
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

export { CategoryTreeFilter };
