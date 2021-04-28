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

  const newChildren = parentNode.children;
  newChildren.splice(newPosition, 0, newNode.identifier);

  const newParentNode = {
    ...parentNode,
    children: newChildren,
  };

  return [...treeNodes.filter(node => node.identifier !== newParentNode.identifier), newParentNode, newNode];
};

const update = <T>(treeNodes: TreeNode<T>[], updatedNode: TreeNode<T>): TreeNode<T>[] => {
  return [...treeNodes.filter(node => node.identifier !== updatedNode.identifier), updatedNode];
};

export {findByIdentifiers, findOneByIdentifier, findRoot, insert, update};
