import React, {createContext, FC, useEffect, useState} from 'react';
import {buildTreeNodeFromCategoryTree, CategoryTreeModel, TreeNode} from '../../models';
import {findOneByIdentifier} from '../../helpers';

type DraggedCategory = {
  parentId: number;
  position: number;
  identifier: number;
  status: 'pending' | 'ready';
};

type HoveredCategory = {
  parentId: number;
  position: number;
  identifier: number;
};

type MoveTarget = {
  position: 'after' | 'before' | 'in';
  parentId: number;
  identifier: number;
};

type CategoryTreeState = {
  nodes: TreeNode<CategoryTreeModel>[];
  setNodes: (nodes: TreeNode<CategoryTreeModel>[]) => void;
  draggedCategory: DraggedCategory | null;
  setDraggedCategory: (data: DraggedCategory | null) => void;
  hoveredCategory: HoveredCategory | null;
  setHoveredCategory: (data: HoveredCategory | null) => void;
  moveTarget: MoveTarget | null;
  setMoveTarget: (data: MoveTarget | null) => void;
};

const CategoryTreeContext = createContext<CategoryTreeState>({
  nodes: [],
  setNodes: () => {},
  draggedCategory: null,
  setDraggedCategory: () => {},
  hoveredCategory: null,
  setHoveredCategory: () => {},
  moveTarget: null,
  setMoveTarget: () => {},
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
  const [moveTarget, setMoveTarget] = useState<MoveTarget | null>(null);

  useEffect(() => {
    if (draggedCategory === null || draggedCategory.status !== 'pending') {
      return;
    }
    const parentCategory = findOneByIdentifier(nodes, draggedCategory.parentId);

    if (!parentCategory) {
      return;
    }

    const position = parentCategory.childrenIds.indexOf(draggedCategory.identifier);

    setDraggedCategory({
      ...draggedCategory,
      status: 'ready',
      position: position >= 0 ? position : 0,
    });
  }, [draggedCategory]);

  const state = {
    nodes,
    setNodes,
    draggedCategory,
    setDraggedCategory,
    hoveredCategory,
    setHoveredCategory,
    moveTarget,
    setMoveTarget,
  };
  return <CategoryTreeContext.Provider value={state}>{children}</CategoryTreeContext.Provider>;
};

export type {DraggedCategory, HoveredCategory, MoveTarget, CategoryTreeState};
export {CategoryTreeProvider, CategoryTreeContext};
