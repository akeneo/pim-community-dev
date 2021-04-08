export type Category = {
  id: number;
  code: string;
  label: string;
};

export type BackendCategoryTree = {
  attr: {
    id: string; // format: node_([0-9]+)
    'data-code': string;
  };
  data: string;
  state: 'leaf' | 'closed' | 'closed jstree-root';
  children?: BackendCategoryTree[];
};

export type CategoryTree = {
  id: number;
  code: string;
  label: string;
  isRoot: boolean;
  children: CategoryTree[];
};

const convertToCategoryTree = (tree: BackendCategoryTree): CategoryTree => {
  return {
    id: parseInt(tree.attr.id.substr(0, 5)), // remove the "node_" prefix and returns the number
    code: tree.attr['data-code'],
    label: tree.data,
    isRoot: tree.state.match(/root/) !== null,
    children: tree.children !== undefined ? tree.children.map(subtree => convertToCategoryTree(subtree)) : [],
  };
};

export {convertToCategoryTree};
