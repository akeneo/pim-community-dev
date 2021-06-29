import React from 'react';
import {ParentCategoryTree, CategoryTreeModel} from './CategoryTree';
import {Tree} from 'akeneo-design-system/lib/components/Tree/Tree';

type CategoryValue = {
  id: number;
  code: string;
  label: string;
};

type RecursiveCategoryTreeProps = {
  tree: CategoryTreeModel;
  childrenCallback: (value: any, parentCategory: ParentCategoryTree) => Promise<CategoryTreeModel[]>;
  onChange?: (value: string, checked: boolean) => void;
  onClick?: any;
  isCategorySelected?: (category: CategoryValue, categoryParentTree: ParentCategoryTree) => boolean;
  isCategoryReadOnly?: (category: CategoryTreeModel, categoryParentTree: ParentCategoryTree) => boolean;
  shouldRerender?: boolean
};

const RecursiveCategoryTree: React.FC<RecursiveCategoryTreeProps> = ({
  tree,
  childrenCallback,
  onChange,
  onClick,
  isCategorySelected,
  isCategoryReadOnly,
  shouldRerender,
}) => {
  const [categoryState, setCategoryState] = React.useState<CategoryTreeModel>(tree);

  const handleOpen = React.useCallback(() => {
    if (typeof categoryState.children === 'undefined') {
      setCategoryState(currentCategoryState => ({...currentCategoryState, loading: true}));
      childrenCallback(categoryState.id, tree.parent ?? null).then(children => {
        setCategoryState(currentCategoryState => ({...currentCategoryState, loading: false, children}));
      });
    }
  }, [categoryState, setCategoryState, childrenCallback]);

  const handleChange = (categoryValue: CategoryValue, checked: boolean) => {
    setCategoryState({...categoryState, selected: checked});
    onChange?.(categoryValue.code, checked);
  };

  return (
    <Tree<CategoryValue>
      label={categoryState.label}
      value={{
        id: categoryState.id,
        code: categoryState.code,
        label: categoryState.label,
      }}
      selected={isCategorySelected ? isCategorySelected(categoryState, tree.parent ?? null) : categoryState.selected}
      isLoading={categoryState.loading}
      readOnly={isCategoryReadOnly ? isCategoryReadOnly(categoryState, tree.parent ?? null) : categoryState.readOnly}
      selectable={categoryState.selectable}
      isLeaf={Array.isArray(categoryState.children) && categoryState.children.length === 0}
      onChange={handleChange}
      onOpen={handleOpen}
      onClick={onClick}
    >
      {categoryState.children &&
        categoryState.children.map(childNode => {
          return (
            <RecursiveCategoryTree
              key={childNode.id}
              tree={childNode}
              onChange={onChange}
              childrenCallback={childrenCallback}
              onClick={onClick}
              isCategorySelected={isCategorySelected}
              isCategoryReadOnly={isCategoryReadOnly}
              shouldRerender={shouldRerender}
            />
          );
        })}
    </Tree>
  );
};

export type {CategoryValue};
export {RecursiveCategoryTree};
