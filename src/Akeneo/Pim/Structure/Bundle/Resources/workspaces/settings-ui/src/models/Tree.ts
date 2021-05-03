export type TreeNode<T> = {
  identifier: number;
  label: string;
  parent: number | null;
  children: number[];
  data: T;
  type?: 'leaf' | 'root' | 'node';
  selected: boolean;
  childrenStatus: 'idle' | 'loaded' | 'loading';
};
