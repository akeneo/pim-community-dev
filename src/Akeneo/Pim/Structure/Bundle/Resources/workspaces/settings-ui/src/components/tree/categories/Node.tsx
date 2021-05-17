import React, {FC} from 'react';
import {Tree} from '../../shared';
import {CategoryTreeModel as CategoryTreeModel} from '../../../models';
import {useCategoryTreeNode, useCountProductsBeforeDeleteCategory} from '../../../hooks';
import {MoveTarget} from '../../providers';
import {Button} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';

type Props = {
  id: number;
  label: string;
  sortable?: boolean;
  followCategory?: (category: CategoryTreeModel) => void;
  onCategoryMoved?: () => void;
  addCategory?: (parentCode: string, onCreate: () => void) => void;
  deleteCategory?: (identifier: number, label: string, numberOfProducts: number, onDelete: () => void) => void;
};

const Node: FC<Props> = ({id, label, followCategory, addCategory, deleteCategory, sortable = false}) => {
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
    resetMove,
    onDeleteCategory,
    onCreateCategory,
    isOpen,
    open,
    close,
  } = useCategoryTreeNode(id);

  const translate = useTranslate();
  const countProductsBeforeDeleteCategory = useCountProductsBeforeDeleteCategory(id);

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
      placeholderPosition={
        moveTarget !== null &&
        node.identifier === moveTarget.identifier &&
        draggedCategory !== null &&
        draggedCategory.identifier !== node.identifier &&
        moveTarget.position !== 'in'
          ? moveTarget.position === 'before'
            ? 'top'
            : 'bottom'
          : undefined
      }
      isOpen={node.type === 'root' ? true : isOpen}
      open={() => {
        open();
        if (node.childrenStatus === 'idle') {
          loadChildren();
        }
      }}
      close={close}
      draggable={sortable && node.type !== 'root'}
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
      }}
      onDrop={async () => {
        if (draggedCategory && moveTarget) {
          moveTo(draggedCategory.identifier, moveTarget, resetMove);
        }
      }}
      onDragEnd={() => resetMove()}
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
                addCategory(node.data.code, onCreateCategory);
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
      {children.map(child => (
        <React.Fragment key={`category-node-${id}-${child.identifier}`}>
          <Node
            id={child.identifier}
            label={child.label}
            followCategory={followCategory}
            addCategory={addCategory}
            deleteCategory={deleteCategory}
            sortable={sortable}
          />
        </React.Fragment>
      ))}
    </Tree>
  );
};
export {Node};
