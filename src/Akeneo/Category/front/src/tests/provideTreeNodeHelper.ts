import {DraggedNode, TreeNode} from '../feature/models';

const aTreeNode = <T>(
  data: T,
  identifier: number = 1234,
  childrenIds: number[] = [],
  label: string = 'a_tree_node',
  code: string = '',
  parentId: number | null = null,
  type: 'root' | 'node' | 'leaf' = 'root',
  childrenStatus: 'idle' | 'loaded' | 'loading' = 'idle'
): TreeNode<T> => {
  return {
    identifier,
    data,
    childrenIds,
    label,
    code,
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
