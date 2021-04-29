import React, {createContext, FC, useState} from 'react';
import {buildTreeNodeFromCategoryTree, CategoryTreeModel, TreeNode} from '../../models';

type DraggedCategory = {
  parentId: number;
  position: number;
  identifier: number;
};

type HoveredCategory = {
  parentId: number;
  position: number;
  identifier: number;
};

type CategoryTreeState = {
  nodes: TreeNode<CategoryTreeModel>[];
  setNodes: (nodes: TreeNode<CategoryTreeModel>[]) => void;
  draggedCategory: DraggedCategory | null;
  setDraggedCategory: (data: DraggedCategory | null) => void;
  hoveredCategory: HoveredCategory | null;
  setHoveredCategory: (data: HoveredCategory | null) => void;
};

const CategoryTreeContext = createContext<CategoryTreeState>({
  nodes: [],
  setNodes: () => {},
  draggedCategory: null,
  setDraggedCategory: () => {},
  hoveredCategory: null,
  setHoveredCategory: () => {},
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
  const [draggedCategory, setDraggedCategory] = useState<DraggedCategory | null>(null);
  const [hoveredCategory, setHoveredCategory] = useState<HoveredCategory | null>(null);

  const state = {
    nodes,
    setNodes,
    draggedCategory,
    setDraggedCategory,
    hoveredCategory,
    setHoveredCategory,
  };
  return <CategoryTreeContext.Provider value={state}>{children}</CategoryTreeContext.Provider>;
};

export {CategoryTreeProvider, CategoryTreeState, CategoryTreeContext};
