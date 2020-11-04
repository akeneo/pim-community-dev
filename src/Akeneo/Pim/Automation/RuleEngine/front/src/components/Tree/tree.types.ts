enum NodeType {
  'LEAF',
  'BRANCH',
}

const isBranch = (nodeType: NodeType) => nodeType === NodeType.BRANCH;

export {isBranch, NodeType};
