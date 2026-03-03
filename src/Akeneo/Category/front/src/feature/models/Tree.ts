type TreeNode<T> = {
  identifier: number;
  label: string;
  code: string;
  parentId: number | null;
  childrenIds: number[];
  data: T;
  type: 'leaf' | 'root' | 'node';
  childrenStatus: 'idle' | 'loaded' | 'loading' | 'to-reload';
};

type PlaceholderPosition = 'bottom' | 'top' | 'middle' | 'none';

type DraggedNode = {
  parentId: number;
  position: number;
  identifier: number;
};

type HoveredNode = {
  parentId: number;
  position: number;
  identifier: number;
};

type DropTargetPosition = 'after' | 'before' | 'in';

type DropTarget = {
  position: DropTargetPosition;
  parentId: number;
  identifier: number;
};

export type {TreeNode, PlaceholderPosition, DraggedNode, HoveredNode, DropTargetPosition, DropTarget};
