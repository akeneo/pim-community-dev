import React, {createContext, FC, useState} from 'react';
import {buildTreeNodeFromCategoryTree, CategoryTreeModel, TreeNode} from '../../models';

type CategoryTreeState = {
  nodes: TreeNode<CategoryTreeModel>[];
  setNodes: (nodes: TreeNode<CategoryTreeModel>[]) => void;
};

const CategoryTreeContext = createContext<CategoryTreeState>({
  nodes: [],
  setNodes: () => {},
});

type Props = {
  root: CategoryTreeModel;
};

const CategoryTreeProvider: FC<Props> = ({children, root}) => {
  const initialNodes = [buildTreeNodeFromCategoryTree(root)];
  if (Array.isArray(root.children)) {
    root.children.forEach(child => {
      initialNodes.push(buildTreeNodeFromCategoryTree(child, root.id));
    });
  }
  const [nodes, setNodes] = useState<TreeNode<CategoryTreeModel>[]>(initialNodes);

  const state = {
    nodes,
    setNodes
  };
  return <CategoryTreeContext.Provider value={state}>{children}</CategoryTreeContext.Provider>;
};

export {CategoryTreeProvider, CategoryTreeState, CategoryTreeContext};
