import React, {FC} from 'react';
import {Tree} from '../../shared';
import {CategoryTreeModel as CategoryTreeModel} from '../../../models';
import {useCategoryTreeNode} from '../../../hooks';
import {MoveTarget} from '../../providers';

type Props = {
  id: number;
  label: string;
  sortable?: boolean;
  followCategory?: (category: CategoryTreeModel) => void;
  // @todo define onCategoryMoved arguments
  onCategoryMoved?: () => void;
};

const Node: FC<Props> = ({id, label, followCategory, sortable = false}) => {
  const {
    node,
    children,
    loadChildren,
    moveTo,
    draggedCategory,
    setDraggedCategory,
    hoveredCategory,
    setHoveredCategory,
    getCategoryPosition,
    moveTarget,
    setMoveTarget,
  } = useCategoryTreeNode(id);

  if (node === undefined) {
    return null;
  }

  return (
    <Tree
      value={node}
      label={label}
      _isRoot={node.type === 'root'}
      isLeaf={node.type === 'leaf'}
      onClick={!followCategory ? undefined : ({data}) => followCategory(data)}
      disabled={draggedCategory !== null && node.identifier === draggedCategory.identifier}
      selected={hoveredCategory !== null && node.identifier === hoveredCategory.identifier}
      onOpen={async () => loadChildren()}
      draggable={sortable && node.type !== 'root'}
      /* @todo Tree is draggable if Node is draggable and the current node is not the root */
      onDragStart={() => {
        // Root is not draggable
        if (!node?.parent) {
          return;
        }
        setDraggedCategory({
          parentId: node.parent,
          position: 0, // @todo get the real position
          identifier: node?.identifier,
        });
      }}
      onDragOver={(target, cursorPosition) => {
        if (!node?.parent) {
          return;
        }

        if (hoveredCategory && hoveredCategory.identifier === node.identifier) {
          const hoveredCategoryDimensions = target.getBoundingClientRect();
          const middleHeight = (hoveredCategoryDimensions.bottom - hoveredCategoryDimensions.top) / 2;
          const cursorRelativePosition = cursorPosition.y - hoveredCategoryDimensions.top;

          const newMoveTarget: MoveTarget = {
            position: cursorRelativePosition < middleHeight ? 'before' : 'after',
            parentId: node.parent,
            identifier: node?.identifier,
          };

          if (!moveTarget || JSON.stringify(moveTarget) != JSON.stringify(newMoveTarget)) {
            setMoveTarget(newMoveTarget);
          }
          return;
        }

        const hoveredCategoryPosition = getCategoryPosition(node);
        setHoveredCategory({
          parentId: node.parent,
          position: hoveredCategoryPosition,
          identifier: node?.identifier,
        });

        // @todo How to define the target?

        // @todo HOW TO define the target position?
        // top-tier: the position will be "prev"
        // mid-tier: if parent category, the position will be "first child" else, the position will be "next"
        // bottom-tier: the position will be "next"

        // @todo if is a valid target, update the category tree state with the droppable node position and parent id
        // @todo if the hover element is a parent category the position will be 0 and the parent id is the hover element
      }}
      // onDragEnter={() => {
      // @todo if the target is a descendant of the dragged item, drop is not valid
      // }}
      // onDragLeave={() => {
      // @todo update the category tree state with a null droppable node position and null parent id
      // so that we can handle the
      // }}
      onDrop={() => {
        // @todo rework to not have to do all these sanity checks
        if (draggedCategory && node !== undefined && moveTarget) {
          moveTo(draggedCategory.identifier, moveTarget);
          /*
                    @todo pass the moveCategory as a props in CategoryTree
                    const moveSuccess = moveCategory({
                      identifier: draggedCategory.identifier,
                      parentId: moveTarget.parentId,
                      previousCategoryId: node.identifier,
                    });

                    // @todo what we have to do if the callback fails? keep original position
                    console.log(moveSuccess);
           */

          setDraggedCategory(null);
          setHoveredCategory(null);
          setMoveTarget(null);
        }
      }}
      onDragEnd={() => {
        setDraggedCategory(null);
        setHoveredCategory(null);
        setMoveTarget(null);
      }}
    >
      {/* @todo if the droppable node position and parent id correspond, add a visual feedback here for the further moved category */}
      {/* @todo handle preview, dragOver, dop, ... of the category when the user moving it */}
      {children.map(child => (
        <React.Fragment key={`category-node-${id}-${child.identifier}`}>
          {moveTarget?.identifier === child.identifier && moveTarget.position === 'before' && (
            <hr style={{borderColor: 'green'}} />
          )}
          <Node
            id={child.identifier}
            label={child.label}
            followCategory={followCategory}
            sortable={sortable}
            /* @todo Node is draggable if the parent Node is draggable */
          />
          {moveTarget?.identifier === child.identifier && moveTarget.position === 'after' && (
            <hr style={{borderColor: 'green'}} />
          )}
        </React.Fragment>
      ))}
    </Tree>
  );
};
export {Node};
