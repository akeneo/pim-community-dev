import {TreeNode} from '../models';

const findRoot = <T>(treeNodes: TreeNode<T>[]): TreeNode<T> | undefined => {
  return treeNodes.find(treeNode => treeNode.isRoot);
};

const findByIdentifiers = <T>(treeNodes: TreeNode<T>[], identifiers: number[]): TreeNode<T>[] => {
  return treeNodes.filter(treeNode => identifiers.includes(treeNode.identifier));
};

const findOneByIdentifier = <T>(treeNodes: TreeNode<T>[], identifier: number): TreeNode<T> | undefined => {
  return findByIdentifiers(treeNodes, [identifier])[0] || undefined;
};

const insert = <T>(treeNodes: TreeNode<T>[], node: TreeNode<T>, parent: number, position: number): TreeNode<T>[] => {
  const parentNode = findOneByIdentifier(treeNodes, parent);
  if (!parentNode) {
    console.error(`The parent node ${parent} not found`);
    return treeNodes;
  }

  if (findByIdentifiers(treeNodes, [node.identifier]).length > 0) {
    console.error(`The node ${node.identifier} already exist`);
    return treeNodes;
  }

  const newNode = {
    ...node,
    parent,
  };

  let newPosition = 0;
  if (position > 0) {
    newPosition = position <= parentNode.children.length ? position : parentNode.children.length;
  }

  const newParentNode = {
    ...parentNode,
    children: parentNode.children.splice(newPosition, 0, newNode.identifier),
  };

  return [...treeNodes.filter(node => node.identifier !== newParentNode.identifier), newParentNode, newNode];
};

export {findByIdentifiers, findOneByIdentifier, findRoot, insert};
