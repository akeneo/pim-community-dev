import {useCallback, useContext, useMemo} from 'react';
import {OrderableTreeContext} from '../components/providers/OrderableTreeProvider';
import {TreeNode} from '../models';

const useDragTreeNode = <T>(node: TreeNode<T> | undefined, index: number) => {
  const {draggedNode, setDraggedNode, isActive} = useContext(OrderableTreeContext);

  const isDraggable = useMemo(() => isActive && node && node.type !== 'root', [isActive, node]);

  const isDragged = useCallback(() => {
    return draggedNode !== null && node !== undefined && draggedNode.identifier === node.identifier;
  }, [draggedNode, node]);

  const onDragStart = useCallback(() => {
    if (!node) {
      return;
    }

    if (node.parentId === null) {
      throw new Error(`Impossible to drag the node "${node.identifier}", parentId is missing`);
    }

    setDraggedNode({
      identifier: node.identifier,
      parentId: node.parentId,
      position: index,
    });
  }, [node, index, setDraggedNode]);

  return {
    draggedNode,
    isDraggable,
    isDragged,
    onDragStart,
  };
};

export {useDragTreeNode};
