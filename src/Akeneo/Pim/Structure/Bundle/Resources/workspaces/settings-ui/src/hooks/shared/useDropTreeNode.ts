import {useCallback, useContext, useEffect, useState} from 'react';
import {PlaceholderPosition, TreeNode} from '../../models';
import {DropTarget, OrderableTreeContext} from '../../components/shared/providers/OrderableTreeProvider';
import {CursorPosition} from '../../components';

const useDropTreeNode = <T>(
  node: TreeNode<T> | undefined,
  index: number,
  reorder: (identifier: number, target: DropTarget, callback: () => void) => void
) => {
  const {dropTarget, setDropTarget, draggedNode, setDraggedNode} = useContext(OrderableTreeContext);
  const [placeholderPosition, setPlaceholderPosition] = useState<PlaceholderPosition>('none');

  const onDrop = useCallback(() => {
    if (!draggedNode || !dropTarget) {
      return;
    }

    if (!node || node.parentId === null || node.type === 'root') {
      return;
    }

    reorder(draggedNode.identifier, dropTarget, () => {
      setDropTarget(null);
      setDraggedNode(null);
    });
  }, [draggedNode, dropTarget, node]);

  const onDragOver = useCallback(
    (target: Element, cursorPosition: CursorPosition) => {
      if (!node || !draggedNode) {
        return;
      }

      if (draggedNode.identifier === node.identifier) {
        return;
      }

      if (node.parentId === null || node.type === 'root') {
        return;
      }

      const hoveredCategoryDimensions = target.getBoundingClientRect();
      const topTierHeight = (hoveredCategoryDimensions.bottom - hoveredCategoryDimensions.top) / 3;
      const bottomTierHeight = topTierHeight * 2;
      const cursorRelativePosition = cursorPosition.y - hoveredCategoryDimensions.top;

      const newDropTarget: DropTarget = {
        parentId: node.parentId,
        identifier: node.identifier,
        position:
          cursorRelativePosition < topTierHeight
            ? 'before'
            : cursorRelativePosition < bottomTierHeight
            ? 'in'
            : 'after',
      };

      if (!dropTarget || JSON.stringify(dropTarget) != JSON.stringify(newDropTarget)) {
        setDropTarget(newDropTarget);
      }
    },
    [node, draggedNode, dropTarget]
  );

  useEffect(() => {
    if (!dropTarget || !node || dropTarget.identifier !== node.identifier) {
      setPlaceholderPosition('none');
      return;
    }

    let position: PlaceholderPosition = 'none';
    switch (dropTarget.position) {
      case 'after':
        position = 'bottom';
        break;
      case 'before':
        position = 'top';
        break;
      case 'in':
        position = 'middle';
        break;
    }

    setPlaceholderPosition(position);
  }, [node, dropTarget]);

  return {
    dropTarget,
    onDrop,
    onDragOver,
    placeholderPosition,
  };
};

export {useDropTreeNode};
