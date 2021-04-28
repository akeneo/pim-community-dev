import React, {createContext, FC} from 'react';
import {CategoryTreeModel, TreeNode} from '../../models';

type CategoryTreeState = {
  nodes: TreeNode<CategoryTreeModel>[];
};

const CategoryTreeContext = createContext<CategoryTreeState>({
  nodes: [],
});

type Props = {
  tree: CategoryTreeModel;
};

const CategoryTreeProvider: FC<Props> = ({children, tree}) => {
  const nodes: TreeNode<CategoryTreeModel>[] = [
    {
      identifier: tree.id,
      label: tree.label,
      children: Array.isArray(tree.children) ? tree.children.map(child => child.id) : [],
      data: tree,
      isRoot: tree.isRoot,
      parent: null,
      selected: false,
      type: tree.isRoot ? 'root' : 'node', // @todo add check for leaf
    },
  ];

  if (Array.isArray(tree.children)) {
    tree.children.forEach(child => {
      nodes.push({
        identifier: child.id,
        label: child.label,
        children: Array.isArray(child.children) ? child.children.map(child => child.id) : [],
        data: child,
        isRoot: false,
        parent: tree.id,
        selected: false,
        type: 'node', // @todo add check for leaf
      });
    });
  }

  const state = {
    nodes,
  };
  return <CategoryTreeContext.Provider value={state}>{children}</CategoryTreeContext.Provider>;
};

export {CategoryTreeProvider, CategoryTreeState, CategoryTreeContext};
