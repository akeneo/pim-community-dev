import React, {createContext, FC, useEffect, useState} from 'react';
import {CategoryTreeModel, TreeNode} from '../../models';
import {buildNodesFromCategoryTree} from '../../helpers';

type CategoryTreeState = {
  nodes: TreeNode<CategoryTreeModel>[];
  setNodes: React.Dispatch<React.SetStateAction<TreeNode<CategoryTreeModel>[]>>;
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
    const nodes = buildNodesFromCategoryTree(root);
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
