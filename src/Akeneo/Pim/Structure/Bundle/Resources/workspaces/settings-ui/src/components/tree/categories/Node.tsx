import React, {FC} from 'react';
import {Tree} from '../../shared';
import {CategoryTreeModel as CategoryTreeModel} from '../../../models';
import {useCategoryTreeNode} from '../../../hooks';

type Props = {
  id: number;
  label: string;
  // @todo add "draggable" props
  followCategory?: (category: CategoryTreeModel) => void;
  // @todo define onCategoryMoved arguments
  onCategoryMoved?: () => void;
};

const Node: FC<Props> = ({id, label, followCategory}) => {
  const {
    node,
    children,
    loadChildren,
    moveAfter,
    draggedCategory,
    setDraggedCategory,
    hoveredCategory,
    setHoveredCategory,
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
      /* @todo if Node is the droppable category, define as "selected" */
      /* @todo if Node is the dragged category, define as "disabled" */
      disabled={draggedCategory !== null && node.identifier === draggedCategory.identifier}
      selected={hoveredCategory !== null && node.identifier === hoveredCategory.identifier}
      onOpen={async () => loadChildren()}
      /* @todo Tree is draggable if Node is draggable and the current node is not the root */
      onDragStart={() => {
        // Root is not draggable
        if (!node?.parent) {
          return;
        }
        setDraggedCategory({
          parentId: node?.parent,
          position: 0, // @todo get the real position
          identifier: node?.identifier,
        });
      }}
      onDragOver={() => {
        // console.log(`dragover ${label}`, node?.identifier);
        if (!node?.parent) {
          return;
        }
        setHoveredCategory({
          parentId: node?.parent,
          position: 0, // @todo get the real position
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
        if (draggedCategory) {
          moveAfter(draggedCategory.identifier, node);
          setDraggedCategory(null);
          setHoveredCategory(null);
        }
        // @todo call onCategoryMoved with draggedId, target parent id and position
        // @todo what we have to do if the callback fails? keep original position
      }}
      onDragEnd={() => {
        setDraggedCategory(null);
        setHoveredCategory(null);
      }}
    >
      {/* @todo if the droppable node position and parent id correspond, add a visual feedback here for the further moved category */}
      {children.map(child => (
        <Node
          key={`category-node-${id}-${child.identifier}`}
          id={child.identifier}
          label={child.label}
          followCategory={followCategory}
          /* @todo Node is draggable if the parent Node is draggable */
        />
      ))}
    </Tree>
  );
};
export {Node};
