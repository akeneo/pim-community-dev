import React, {createContext, FC, useCallback, useState} from 'react';
import {DraggedNode, DropTarget, HoveredNode} from '../../models';

type OrderableTreeState = {
  draggedNode: DraggedNode | null;
  setDraggedNode: (data: DraggedNode | null) => void;
  endMove: () => void;
  isActive: boolean;
};

const OrderableTreeContext = createContext<OrderableTreeState>({
  draggedNode: null,
  setDraggedNode: () => {},
  endMove: () => {},
  isActive: false,
});

type Props = {
  isActive: boolean;
};

const OrderableTreeProvider: FC<Props> = ({children, isActive}) => {
  const [draggedNode, setDraggedNode] = useState<DraggedNode | null>(null);

  const endMove = useCallback(() => {
    setDraggedNode(null);
  }, []);

  const state = {
    draggedNode,
    setDraggedNode,
    endMove,
    isActive,
  };
  return <OrderableTreeContext.Provider value={state}>{children}</OrderableTreeContext.Provider>;
};

export type {DraggedNode, HoveredNode, DropTarget, OrderableTreeState};
export {OrderableTreeProvider, OrderableTreeContext};
