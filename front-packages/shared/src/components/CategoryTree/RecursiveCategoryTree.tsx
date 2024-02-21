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
  onClick?: any;
  isCategorySelected?: (category: CategoryValue, categoryParentTree: ParentCategoryTree) => boolean;
  isCategoryReadOnly?: (category: CategoryTreeModel, categoryParentTree: ParentCategoryTree) => boolean;
  internalSetChildren: (value: string, children: CategoryTreeModel[]) => void;
  internalSetChecked: (value: string, checked: boolean) => void;
};

const RecursiveCategoryTree: React.FC<RecursiveCategoryTreeProps> = ({
  tree,
  parentTree,
  childrenCallback,
  onClick,
  isCategorySelected,
  isCategoryReadOnly,
  internalSetChecked,
  internalSetChildren,
  ...rest
}) => {
  const [loading, setIsLoading] = React.useState<boolean>(tree.loading ?? false);

  const handleOpen = React.useCallback(() => {
    if (typeof tree.children === 'undefined') {
      setIsLoading(true);
      childrenCallback(tree.id).then(children => {
        setIsLoading(false);
        internalSetChildren(tree.code, children);
      });
    }
  }, [tree, internalSetChildren, childrenCallback]);

  const handleChange = (categoryValue: CategoryValue, checked: boolean) => {
    internalSetChecked(categoryValue.code, checked);
  };

  return (
    <Tree<CategoryValue>
      label={tree.label}
      value={{
        id: tree.id,
        code: tree.code,
        label: tree.label,
      }}
      isLoading={loading}
      selected={isCategorySelected ? isCategorySelected(tree, parentTree) : tree.selected}
      readOnly={isCategoryReadOnly ? isCategoryReadOnly(tree, parentTree) : tree.readOnly}
      selectable={tree.selectable}
      isLeaf={Array.isArray(tree.children) && tree.children.length === 0}
      onChange={handleChange}
      onOpen={handleOpen}
      onClick={onClick}
      {...rest}
    >
      {tree.children &&
        tree.children.map(childNode => {
          return (
            <RecursiveCategoryTree
              key={childNode.id}
              tree={childNode}
              parentTree={{code: tree.code, parent: parentTree}}
              childrenCallback={childrenCallback}
              onClick={onClick}
              isCategorySelected={isCategorySelected}
              isCategoryReadOnly={isCategoryReadOnly}
              internalSetChildren={internalSetChildren}
              internalSetChecked={internalSetChecked}
            />
          );
        })}
    </Tree>
  );
};

export type {CategoryValue};
export {RecursiveCategoryTree};
