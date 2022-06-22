import {buildTreeNodeFromCategoryTree, CategoryTreeModel, TreeNode} from '../models';

const findRoot = <T>(treeNodes: TreeNode<T>[]): TreeNode<T> | undefined => {
  return treeNodes.find(treeNode => treeNode.type === 'root');
};

const findByIdentifiers = <T>(treeNodes: TreeNode<T>[], identifiers: number[]): TreeNode<T>[] => {
  const nodes = treeNodes.filter(treeNode => identifiers.includes(treeNode.identifier));

  return identifiers
    .map(identifier => nodes.find(n => n.identifier === identifier))
    .filter(node => !!node) as TreeNode<T>[];
};

const findOneByIdentifier = <T>(treeNodes: TreeNode<T>[], identifier: number): TreeNode<T> | undefined => {
  return findByIdentifiers(treeNodes, [identifier])[0] || undefined;
};

const isDescendantOf = <T>(treeNodes: TreeNode<T>[], identifier: number, parentId: number): boolean => {
  const node = findOneByIdentifier(treeNodes, identifier);
  if (!node || node.parentId === null) {
    return false;
  }

  if (node.parentId === parentId) {
    return true;
  }

  return isDescendantOf(treeNodes, identifier, node.parentId);
};

const findLoadedDescendantsIdentifiers = <T>(treeNodes: TreeNode<T>[], parent: TreeNode<T>): number[] => {
  if (parent.childrenIds.length === 0 || parent.childrenStatus !== 'loaded') {
    return [];
  }

  let descendantsIdentifiers: number[] = [];
  parent.childrenIds.forEach((childId: number) => {
    descendantsIdentifiers.push(childId);

    const child = findOneByIdentifier(treeNodes, childId);
    if (child) {
      descendantsIdentifiers = [...descendantsIdentifiers, ...findLoadedDescendantsIdentifiers(treeNodes, child)];
    }
  });

  return descendantsIdentifiers;
};

const update = <T>(treeNodes: TreeNode<T>[], updatedNode: TreeNode<T>): TreeNode<T>[] => {
  return [...treeNodes.filter(node => node.identifier !== updatedNode.identifier), updatedNode];
};

const buildNodesFromCategoryTree = (root: CategoryTreeModel) => {
  const nodes = [buildTreeNodeFromCategoryTree(root)];
  if (Array.isArray(root.children)) {
    addToNodes(nodes, root.children, root.id);
  }

  return nodes;
};

const addToNodes = (nodes: TreeNode<CategoryTreeModel>[], children: CategoryTreeModel[], parentId: number) => {
  children.forEach(child => {
    nodes.push(buildTreeNodeFromCategoryTree(child, parentId));
    if (child.children) {
      addToNodes(nodes, child.children, child.id);
    }
  });

  return nodes;
};

export {
  findByIdentifiers,
  findOneByIdentifier,
  findRoot,
  update,
  isDescendantOf,
  findLoadedDescendantsIdentifiers,
  buildNodesFromCategoryTree,
};
