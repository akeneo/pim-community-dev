type CategoryTreeModel = {
  code: string;
  id: number;
  labels: {[key: string]: string};
  parent: string | null;
};

type CategoryTreeNodeAttr = {
  id: number;
  'data-code': string;
};

type CategoryTreeNodeModel = {
  attr: CategoryTreeNodeAttr;
  children?: CategoryTreeNodeModel[];
  data: string;
  state: string;
};

type CategoryTreeModelWithOpenBranch = {
  children: CategoryTreeModelWithOpenBranch[];
  selectedChildCount: number;
} & CategoryTreeNodeModel;

export {
  CategoryTreeModel,
  CategoryTreeNodeModel,
  CategoryTreeModelWithOpenBranch,
};
