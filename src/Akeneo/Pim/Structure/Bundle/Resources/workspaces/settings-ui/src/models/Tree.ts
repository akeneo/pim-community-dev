export type TreeNode<T> = {
  identifier: number;
  label: string;
  parent: number | null;
  children: number[];
  data: T;
  isRoot: boolean;
  type?: 'leaf' | 'root' | 'node';
  selected: boolean;
  // @todo add data loaded status?
};
