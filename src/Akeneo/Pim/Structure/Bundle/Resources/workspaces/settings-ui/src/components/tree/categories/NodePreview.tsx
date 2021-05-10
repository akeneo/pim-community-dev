import React, {FC} from 'react';
import {Tree} from '../../shared';
import {useCategoryTreeNode} from '../../../hooks';

type Props = {
  id: number;
};

const NodePreview: FC<Props> = ({id}) => {
  const {node, draggedCategory, moveTarget, moveTo} = useCategoryTreeNode(id);

  if (node === undefined) {
    return null;
  }
  return (
    <Tree
      value={node}
      label={node.label}
      _isRoot={false}
      isLeaf={node.type === 'leaf'}
      selected={true}
      onDrop={() => {
        if (draggedCategory && moveTarget && draggedCategory.identifier !== moveTarget.identifier) {
          moveTo(draggedCategory.identifier, moveTarget);
          // @todo persist in backend
        }
      }}
    />
  );
};

export {NodePreview};
