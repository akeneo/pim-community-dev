import {TreeNode} from '../models';

const findRoot = <T>(treeNodes: TreeNode<T>[]): TreeNode<T> | undefined => {
  return treeNodes.find(treeNode => treeNode.type === 'root');
};

const findByIdentifiers = <T>(treeNodes: TreeNode<T>[], identifiers: number[]): TreeNode<T>[] => {
  const nodes = treeNodes.filter(treeNode => identifiers.includes(treeNode.identifier));

  const result: TreeNode<T>[] = [];
  identifiers.map(identifier => {
    const node = nodes.find(node => node.identifier === identifier);
    if (node !== undefined) {
      result.push(node);
    }
  });

  return result;
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

  let descendantsIdentifiers = [];
  parent.childrenIds.map((childId: number) => {
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

export {findByIdentifiers, findOneByIdentifier, findRoot, update, isDescendantOf, findLoadedDescendantsIdentifiers};
