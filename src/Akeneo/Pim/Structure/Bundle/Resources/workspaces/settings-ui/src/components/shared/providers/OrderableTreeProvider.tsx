import React, {createContext, FC, useCallback, useState} from 'react';
import {DraggedNode, DropTarget, HoveredNode} from '../../../models';

type OrderableTreeState = {
  draggedNode: DraggedNode | null;
  setDraggedNode: (data: DraggedNode | null) => void;
  dropTarget: DropTarget | null;
  setDropTarget: (data: DropTarget | null) => void;
  endMove: () => void;
  isActive: boolean;
};

const OrderableTreeContext = createContext<OrderableTreeState>({
  draggedNode: null,
  setDraggedNode: () => {},
  dropTarget: null,
  setDropTarget: () => {},
  endMove: () => {},
  isActive: false,
});

type Props = {
  isActive: boolean;
};

const OrderableTreeProvider: FC<Props> = ({children, isActive}) => {
  const [draggedNode, setDraggedNode] = useState<DraggedNode | null>(null);
  const [dropTarget, setDropTarget] = useState<DropTarget | null>(null);

  const endMove = useCallback(() => {
    setDraggedNode(null);
    setDropTarget(null);
  }, []);

  const state = {
    draggedNode,
    setDraggedNode,
    dropTarget,
    setDropTarget,
    endMove,
    isActive,
  };
  return <OrderableTreeContext.Provider value={state}>{children}</OrderableTreeContext.Provider>;
};

export type {DraggedNode, HoveredNode, DropTarget, OrderableTreeState};
export {OrderableTreeProvider, OrderableTreeContext};
