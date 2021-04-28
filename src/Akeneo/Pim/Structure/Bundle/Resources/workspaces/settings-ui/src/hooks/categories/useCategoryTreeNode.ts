import {useContext, useEffect, useState} from 'react';
import {CategoryTreeContext} from '../../components';
import {
  BackendCategoryTree,
  buildTreeNodeFromCategoryTree,
  CategoryTreeModel,
  convertToCategoryTree,
  TreeNode,
} from '../../models';
import {findByIdentifiers, findOneByIdentifier, update} from '../../helpers/treeHelper';
import {useFetch, useRoute} from '@akeneo-pim-community/shared';

const useCategoryTreeNode = (id: number) => {
  const {nodes, setNodes} = useContext(CategoryTreeContext);
  const [node, setNode] = useState<TreeNode<CategoryTreeModel> | undefined>(undefined);
  const [children, setChildren] = useState<TreeNode<CategoryTreeModel>[]>([]);

  const url = useRoute('pim_enrich_categorytree_children', {
    _format: 'json',
    id: id.toString(),
    select_node_id: '-1',
    with_items_count: '0',
    include_parent: '0',
    include_sub: '0',
  });

  const {data, fetch, error: fetchError, status: fetchStatus} = useFetch<BackendCategoryTree>(url);

  useEffect(() => {
    setNode(findOneByIdentifier(nodes, id));
  }, [id, nodes]);

  useEffect(() => {
    if (!node) {
      return;
    }
    setChildren(findByIdentifiers(nodes, node.children));
  }, [node, nodes]);

  useEffect(() => {
    if (!node || !Array.isArray(data)) {
      return;
    }
    const newChildren: TreeNode<CategoryTreeModel>[] = data.map(child => {
      return buildTreeNodeFromCategoryTree(convertToCategoryTree(child), node.identifier);
    });

    const updatedNodes = update(nodes, {
      ...node,
      children: newChildren.map(child => child.identifier)
    });

    // @todo check uniqueness of new children
    setNodes([...updatedNodes, ...newChildren]);
  }, [data]);

  return {
    node,
    children,
    loadChildren: fetch,
  };
};

export {useCategoryTreeNode};
