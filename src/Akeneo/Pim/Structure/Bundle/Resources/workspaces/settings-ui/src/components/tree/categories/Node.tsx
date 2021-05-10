import React, {FC, useCallback} from 'react';
import {Tree} from '../../shared';
import {CategoryTreeModel as CategoryTreeModel} from '../../../models';
import {useCategoryTreeNode} from '../../../hooks';
import {MoveTarget} from '../../providers';
import {Button} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {useCountProductsBeforeDeleteCategory} from '../../../hooks';
import {NodePreview} from './NodePreview';
import {move} from 'formik';

type Props = {
  id: number;
  label: string;
  sortable?: boolean;
  followCategory?: (category: CategoryTreeModel) => void;
  // @todo define onCategoryMoved arguments
  onCategoryMoved?: () => void;
  addCategory?: (parentCode: string, onCreate: () => void) => void;
  deleteCategory?: (identifier: number, label: string, numberOfProducts: number, onDelete: () => void) => void;
};

const Node: FC<Props> = ({id, label, followCategory, addCategory, deleteCategory, sortable = false}) => {
  const {
    node,
    children,
    loadChildren,
    forceReloadChildren,
    moveTo,
    draggedCategory,
    setDraggedCategory,
    hoveredCategory,
    setHoveredCategory,
    getCategoryPosition,
    moveTarget,
    setMoveTarget,
    onDeleteCategory,
  } = useCategoryTreeNode(id);

  const translate = useTranslate();
  const countProductsBeforeDeleteCategory = useCountProductsBeforeDeleteCategory(id);

  const isValidPreviewPosition = useCallback(
    (position: number): boolean => {
      if (draggedCategory === null || moveTarget === null || draggedCategory.status !== 'ready') {
        return false;
      }

      if (draggedCategory.identifier === moveTarget.identifier) {
        return false;
      }

      const previewPosition =
        moveTarget.position === 'before' ? position - 1 : moveTarget.position === 'after' ? position + 1 : position;
      const isOriginalPosition =
        moveTarget.identifier === draggedCategory.identifier ||
        (draggedCategory.parentId === moveTarget.parentId && draggedCategory.position === previewPosition);

      return !isOriginalPosition;
    },
    [draggedCategory, moveTarget]
  );

  if (node === undefined) {
    return null;
  }

  return (
    <Tree
      value={node}
      label={label}
      _isRoot={node.type === 'root'}
      isLeaf={node.type === 'leaf'}
      isLoading={node.childrenStatus === 'loading'}
      onClick={!followCategory ? undefined : ({data}) => followCategory(data)}
      disabled={draggedCategory !== null && node.identifier === draggedCategory.identifier}
      selected={
        moveTarget !== null &&
        node.identifier === moveTarget.identifier &&
        moveTarget.position === 'in' &&
        draggedCategory !== null &&
        draggedCategory.identifier !== node.identifier
      }
      onOpen={async () => {
        // @todo handle when children have already loaded
        if (node.childrenStatus !== 'idle') {
          return;
        }
        return loadChildren();
      }}
      draggable={sortable && node.type !== 'root'}
      /* @todo Tree is draggable if Node is draggable and the current node is not the root */
      onDragStart={() => {
        // Root is not draggable
        if (!node?.parentId) {
          return;
        }
        setDraggedCategory({
          parentId: node.parentId,
          position: 0,
          status: 'pending',
          identifier: node.identifier,
        });
      }}
      onDragOver={(target, cursorPosition) => {
        if (!node?.parentId) {
          return;
        }

        if (hoveredCategory && hoveredCategory.identifier === node.identifier) {
          const hoveredCategoryDimensions = target.getBoundingClientRect();
          const topTierHeight = (hoveredCategoryDimensions.bottom - hoveredCategoryDimensions.top) / 3;
          const bottomTierHeight = topTierHeight * 2;
          const cursorRelativePosition = cursorPosition.y - hoveredCategoryDimensions.top;

          // top-tier: the position will be "before"
          // mid-tier: the position will be "in"
          // bottom-tier: the position will be "after"
          const newMoveTarget: MoveTarget = {
            position:
              cursorRelativePosition < topTierHeight
                ? 'before'
                : cursorRelativePosition < bottomTierHeight
                ? 'in'
                : 'after',
            parentId: node.parentId,
            identifier: node?.identifier,
          };

          if (!moveTarget || JSON.stringify(moveTarget) != JSON.stringify(newMoveTarget)) {
            setMoveTarget(newMoveTarget);
          }
          return;
        }

        const hoveredCategoryPosition = getCategoryPosition(node);
        setHoveredCategory({
          parentId: node.parentId,
          position: hoveredCategoryPosition,
          identifier: node?.identifier,
        });

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
      onDrop={async () => {
        if (draggedCategory && moveTarget && draggedCategory.identifier !== moveTarget.identifier) {
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

          // @todo: why onDragEnd is not fired when moving "in"?
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
      {(addCategory || deleteCategory) && (
        <Tree.Actions key={`category-actions-${id}`}>
          {addCategory && (
            <Button
              ghost
              level={'primary'}
              size="small"
              onClick={event => {
                event.stopPropagation();
                addCategory(node.data.code, forceReloadChildren);
              }}
            >
              {translate('pim_enrich.entity.category.new_category')}
            </Button>
          )}
          {deleteCategory && node.type !== 'root' && (
            <Button
              ghost
              level={'danger'}
              size="small"
              onClick={event => {
                event.stopPropagation();
                countProductsBeforeDeleteCategory((nbProducts: number) =>
                  deleteCategory(id, label, nbProducts, onDeleteCategory)
                );
              }}
            >
              {translate('pim_common.delete')}
            </Button>
          )}
        </Tree.Actions>
      )}
      {children.map((child, index) => (
        <React.Fragment key={`category-node-${id}-${child.identifier}`}>
          {draggedCategory &&
            moveTarget &&
            moveTarget.identifier === child.identifier &&
            moveTarget.position === 'before' &&
            isValidPreviewPosition(index) && <NodePreview id={draggedCategory.identifier} />}
          <Node
            id={child.identifier}
            label={child.label}
            followCategory={followCategory}
            addCategory={addCategory}
            deleteCategory={deleteCategory}
            sortable={sortable}
          />
          {draggedCategory &&
            moveTarget &&
            moveTarget.identifier === child.identifier &&
            moveTarget.position === 'after' &&
            isValidPreviewPosition(index) && <NodePreview id={draggedCategory.identifier} />}
        </React.Fragment>
      ))}
    </Tree>
  );
};
export {Node};
