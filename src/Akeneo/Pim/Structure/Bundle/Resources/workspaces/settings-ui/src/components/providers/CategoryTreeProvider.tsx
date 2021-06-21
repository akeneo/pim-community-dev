import React, {createContext, FC, useEffect, useState} from 'react';
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
  const [nodes, setNodes] = useState<TreeNode<CategoryTreeModel>[]>([]);

  useEffect(() => {
    const nodes = [buildTreeNodeFromCategoryTree(root)];
    if (Array.isArray(root.children)) {
      root.children.forEach(child => {
        nodes.push(buildTreeNodeFromCategoryTree(child, root.id));
      });
    }
    setNodes(nodes);
  }, [root]);

  const state = {
    nodes,
    setNodes,
  };
  return <CategoryTreeContext.Provider value={state}>{children}</CategoryTreeContext.Provider>;
};

export type {CategoryTreeState};
export {CategoryTreeProvider, CategoryTreeContext};
