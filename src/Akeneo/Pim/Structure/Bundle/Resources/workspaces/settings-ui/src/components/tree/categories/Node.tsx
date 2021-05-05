import React, {FC} from 'react';
import {Tree} from '../../shared';
import {CategoryTreeModel as CategoryTreeModel, TreeNode} from '../../../models';
import {useCategoryTreeNode} from '../../../hooks';
import {MoveTarget} from '../../providers';
import {Button} from 'akeneo-design-system';
import {useTranslate} from "@akeneo-pim-community/shared";

type Props = {
  id: number;
  label: string;
  sortable?: boolean;
  followCategory?: (category: CategoryTreeModel) => void;
  // @todo define onCategoryMoved arguments
  onCategoryMoved?: () => void;
  addCategory?: (parentCode: string, onCategoryAdded: () => {}) => void;
  deleteCategory?: (categoryId: number) => void; // @todo define arguments that we really need
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
  } = useCategoryTreeNode(id);

  const translate = useTranslate();

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
      selected={hoveredCategory !== null && node.identifier === hoveredCategory.identifier}
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
          position: 0, // @todo get the real position
          identifier: node?.identifier,
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
        // @todo rework to not have to do all these sanity checks
        if (draggedCategory && node !== undefined && moveTarget) {
          if (moveTarget.position === 'in' && node.type === 'node' && node.childrenStatus === 'idle') {
            await loadChildren(); // @fixme conflict with the stateful moveTo, the nodes is not refreshed yet
          }

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
                deleteCategory(id);
              }}
            >
              {translate('pim_common.delete')}
            </Button>
          )}
        </Tree.Actions>
      )}
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
            addCategory={addCategory}
            deleteCategory={deleteCategory}
            sortable={sortable}
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
