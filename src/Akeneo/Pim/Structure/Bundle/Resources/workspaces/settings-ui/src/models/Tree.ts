export type TreeNode<T> = {
  identifier: number;
  label: string;
  parentId: number | null;
  childrenIds: number[];
  data: T;
  type: 'leaf' | 'root' | 'node';
  childrenStatus: 'idle' | 'loaded' | 'loading';
};
