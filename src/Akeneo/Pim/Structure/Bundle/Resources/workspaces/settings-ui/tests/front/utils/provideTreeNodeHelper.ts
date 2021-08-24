import {DraggedNode, TreeNode} from '../../../src';

const aTreeNode = <T>(
  data: T,
  identifier: number = 1234,
  childrenIds: number[] = [],
  label: string = 'a_tree_node',
  parentId: number | null = null,
  type: 'root' | 'node' | 'leaf' = 'root',
  childrenStatus: 'idle' | 'loaded' | 'loading' = 'idle'
): TreeNode<T> => {
  return {
    identifier,
    data,
    childrenIds,
    label,
    parentId,
    type,
    childrenStatus,
  };
};

const aDraggedNode = (identifier: number = 1234, parentId: number = 1111, position: number = 0): DraggedNode => {
  return {
    parentId,
    position,
    identifier,
  };
};

export {aTreeNode, aDraggedNode};
