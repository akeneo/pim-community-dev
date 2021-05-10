import React, {FC} from 'react';
import {Tree} from '../../shared';
import {useCategoryTreeNode} from '../../../hooks';

type Props = {
  id: number;
};

const NodePreview: FC<Props> = ({id}) => {
  const {node} = useCategoryTreeNode(id);

  if (node === undefined) {
    return null;
  }
  //return <hr style={{borderColor: 'green'}} />;

  return <Tree value={node} label={node.label} _isRoot={false} isLeaf={node.type === 'leaf'} selected={true} />;
};

export {NodePreview};
