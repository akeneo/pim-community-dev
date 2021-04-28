import React, {createContext, FC} from 'react';
import {buildTreeNodeFromCategoryTree, CategoryTreeModel, TreeNode} from '../../models';

type CategoryTreeState = {
  nodes: TreeNode<CategoryTreeModel>[];
};

const CategoryTreeContext = createContext<CategoryTreeState>({
  nodes: [],
});

type Props = {
  root: CategoryTreeModel;
};

const CategoryTreeProvider: FC<Props> = ({children, root}) => {
  const nodes: TreeNode<CategoryTreeModel>[] = [buildTreeNodeFromCategoryTree(root)];

  if (Array.isArray(root.children)) {
    root.children.forEach(child => {
      nodes.push(buildTreeNodeFromCategoryTree(child, root.id));
    });
  }

  const state = {
    nodes,
  };
  return <CategoryTreeContext.Provider value={state}>{children}</CategoryTreeContext.Provider>;
};

export {CategoryTreeProvider, CategoryTreeState, CategoryTreeContext};
