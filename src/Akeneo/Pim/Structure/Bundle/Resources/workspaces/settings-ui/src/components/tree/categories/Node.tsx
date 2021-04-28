import React, {FC} from 'react';
import {Tree} from '../../shared';
import {CategoryTreeModel as CategoryTreeModel} from '../../../models';
import {useCategoryTreeNode} from '../../../hooks';

type Props = {
  id: number;
  label: string;
  followCategory?: (category: CategoryTreeModel) => void;
};

const Node: FC<Props> = ({id, label, followCategory}) => {
  const {node, children, loadChildren, moveAfter} = useCategoryTreeNode(id);

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
      onOpen={async () => loadChildren()}
      onDrop={(value, draggedId: number) => {
        moveAfter(draggedId, node);

        // call callback to save it in backend
        // what we have to do if the callback fails? keep original position
      }}
    >
      {children.map(child => (
        <Node
          key={`category-node-${id}-${child.identifier}`}
          id={child.identifier}
          label={child.label}
          followCategory={followCategory}
        />
      ))}
    </Tree>
  );
};

export {Node};
