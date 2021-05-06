import {TreeNode} from '../../../src';

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

export {aTreeNode};
