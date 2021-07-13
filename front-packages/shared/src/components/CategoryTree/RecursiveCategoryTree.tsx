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
  parentTree: ParentCategoryTree;
  childrenCallback: (value: any) => Promise<CategoryTreeModel[]>;
  onChange?: (value: string, checked: boolean) => void;
  onClick?: any;
  isCategorySelected?: (category: CategoryValue, categoryParentTree: ParentCategoryTree) => boolean;
  isCategoryReadOnly?: (category: CategoryTreeModel, categoryParentTree: ParentCategoryTree) => boolean;
};

const RecursiveCategoryTree: React.FC<RecursiveCategoryTreeProps> = ({
  tree,
  parentTree,
  childrenCallback,
  onChange,
  onClick,
  isCategorySelected,
  isCategoryReadOnly,
}) => {
  const [categoryState, setCategoryState] = React.useState<CategoryTreeModel>(tree);

  const handleOpen = React.useCallback(() => {
    if (typeof categoryState.children === 'undefined') {
      setCategoryState(currentCategoryState => ({...currentCategoryState, loading: true}));
      childrenCallback(categoryState.id).then(children => {
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
      isLoading={categoryState.loading}
      selected={isCategorySelected ? isCategorySelected(categoryState, parentTree) : categoryState.selected}
      readOnly={isCategoryReadOnly ? isCategoryReadOnly(categoryState, parentTree) : categoryState.readOnly}
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
              parentTree={{code: categoryState.code, parent: parentTree}}
              childrenCallback={childrenCallback}
              onClick={onClick}
              isCategorySelected={isCategorySelected}
              isCategoryReadOnly={isCategoryReadOnly}
            />
          );
        })}
    </Tree>
  );
};

export type {CategoryValue};
export {RecursiveCategoryTree};
