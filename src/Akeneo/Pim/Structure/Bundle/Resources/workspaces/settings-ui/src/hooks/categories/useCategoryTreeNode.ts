import {useContext, useEffect, useState} from 'react';
import {CategoryTreeContext} from '../../components';
import {CategoryTreeModel, TreeNode} from '../../models';
import {findByIdentifiers, findOneByIdentifier} from '../../helpers/treeHelper';

const useCategoryTreeNode = (id: number) => {
  const {nodes} = useContext(CategoryTreeContext);
  const [node, setNode] = useState<TreeNode<CategoryTreeModel> | undefined>(undefined);
  const [children, setChildren] = useState<TreeNode<CategoryTreeModel>[]>([]);

  useEffect(() => {
    setNode(findOneByIdentifier(nodes, id));
  }, [id, nodes]);

  useEffect(() => {
    if (!node) {
      return;
    }
    setChildren(findByIdentifiers(nodes, node.children));
  }, [node, nodes]);

  return {
    node,
    children,
  };
};

export {useCategoryTreeNode};
