import {useCallback, useContext, useEffect, useState} from 'react';
import {PlaceholderPosition, TreeNode} from '../../models';
import {DropTarget, OrderableTreeContext} from '../../components/shared/providers/OrderableTreeProvider';
import {CursorPosition} from '../../components';

const useDrop = <T>(
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

      if (!dropTarget || dropTarget.identifier !== node.identifier) {
        setDropTarget({
          position: 'before',
          parentId: node.parentId,
          identifier: node.identifier,
        });
        return;
      }

      const hoveredCategoryDimensions = target.getBoundingClientRect();
      const topTierHeight = (hoveredCategoryDimensions.bottom - hoveredCategoryDimensions.top) / 3;
      const bottomTierHeight = topTierHeight * 2;
      const cursorRelativePosition = cursorPosition.y - hoveredCategoryDimensions.top;

      const newDropTarget: DropTarget = {
        ...dropTarget,
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
      setPlaceholderPosition(
        dropTarget.position === 'after' ? 'bottom' : dropTarget.position === 'before' ? 'top' : 'middle'
      );
    },
    [node, draggedNode, dropTarget]
  );

  useEffect(() => {
    if (!dropTarget || !node || dropTarget.identifier !== node.identifier) {
      setPlaceholderPosition('none');
      return;
    }
    setPlaceholderPosition(
      dropTarget.position === 'after' ? 'bottom' : dropTarget.position === 'before' ? 'top' : 'middle'
    );
  }, [node, dropTarget]);

  return {
    dropTarget: dropTarget,
    onDrop,
    onDragOver,
    placeholderPosition,
  };
};

export {useDrop};
