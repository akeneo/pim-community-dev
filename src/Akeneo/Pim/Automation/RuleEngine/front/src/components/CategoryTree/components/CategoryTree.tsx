import React from 'react';
import {
  CategoryTreeModel,
  CategoryTreeModelWithOpenBranch,
} from '../category-tree.types';
import {CategoryTreeNode} from './CategoryTreeNode';
import {Category, LocaleCode, CategoryCode} from '../../../models';
import {NodeType} from '../../Tree/tree.types';

type Props = {
  categoryTree: CategoryTreeModel;
  ariaLabelledBy?: string;
  locale: LocaleCode;
  onSelectCategory: (categoryCode: CategoryCode) => void;
  selectedCategories: Category[];
  initCategoryTreeOpenBranch?: CategoryTreeModelWithOpenBranch;
};

const CategoryTree: React.FC<Props> = ({
  categoryTree,
  ariaLabelledBy,
  locale,
  onSelectCategory,
  selectedCategories,
  initCategoryTreeOpenBranch,
}) => {
  const onNodeSelectCategory = (categoryCode: CategoryCode) => {
    if (categoryCode !== categoryTree.code) {
      onSelectCategory(categoryCode);
    }
  };

  return (
    <ul role='tree' aria-labelledby={ariaLabelledBy}>
      <CategoryTreeNode
        initCategoryTreeOpenBranch={initCategoryTreeOpenBranch}
        categoryCode={categoryTree.code}
        categoryId={categoryTree.id}
        categoryLabel={categoryTree.labels[locale] || `[${categoryTree.code}]`}
        categoryRootId={categoryTree.id}
        locale={locale}
        nodeType={NodeType.BRANCH}
        opened
        onSelect={onNodeSelectCategory}
        selectedCategories={selectedCategories}
      />
    </ul>
  );
};

export {CategoryTree};
