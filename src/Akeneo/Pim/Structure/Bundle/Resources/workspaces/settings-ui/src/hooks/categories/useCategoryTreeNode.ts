import {useCallback, useContext, useEffect, useState} from 'react';
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
  const {nodes, setNodes, ...rest} = useContext(CategoryTreeContext);
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

  const getCategoryPosition = (treeNode: TreeNode<CategoryTreeModel>): number => {
    if (!treeNode.parent) {
      return 0;
    }
    const categoryParent = findOneByIdentifier(nodes, treeNode.parent);

    return categoryParent?.children.indexOf(treeNode.identifier) || 0;
  };

  const moveAfter = useCallback(
    (originalId: number, target: TreeNode<CategoryTreeModel>) => {
      // find parent
      // find original node
      // find original parent

      if (!target.parent) {
        console.error('Can not move after root node');
        // @todo handle error
        return;
      }

      const movedNode = findOneByIdentifier(nodes, originalId);
      if (!movedNode) {
        console.error(`Node ${originalId} not found`);
        // @todo handle error
        return;
      }

      const targetParentNode = findOneByIdentifier(nodes, target.parent);
      if (!targetParentNode) {
        console.error(`Node ${target.parent} not found`);
        // @todo handle error
        return;
      }

      if (!movedNode.parent) {
        console.error('Can not move root node');
        // @todo handle error
        return;
      }
      const originalParentNode = findOneByIdentifier(nodes, movedNode.parent);
      if (!originalParentNode) {
        console.error(`Node ${movedNode.parent} not found`);
        // @todo handle error
        return;
      }

      let newNodesList = nodes;

      // update the children of parent
      // We ensure that the moved node is not in the list
      const parentChildrenIds = targetParentNode.children.filter(id => id !== movedNode.identifier);
      const movedIndex = parentChildrenIds.findIndex(id => id === target.identifier);

      parentChildrenIds.splice(movedIndex + 1, 0, movedNode.identifier);

      newNodesList = update(newNodesList, {
        ...targetParentNode,
        children: parentChildrenIds,
      });

      // update parent id for the original node
      newNodesList = update(newNodesList, {
        ...movedNode,
        parent: targetParentNode.identifier,
      });

      // remove the original id from the original parent's children
      // update the original parent
      if (originalParentNode.identifier !== targetParentNode.identifier) {
        newNodesList = update(newNodesList, {
          ...originalParentNode,
          children: targetParentNode.children.filter(id => id !== movedNode.identifier),
        });
      }

      setNodes(newNodesList);

      // call callback to save it in backend
      // what we have to do if the callback fails? keep original position
    },
    [nodes]
  );

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
      children: newChildren.map(child => child.identifier),
    });

    // @todo check uniqueness of new children
    setNodes([...updatedNodes, ...newChildren]);
  }, [data]);

  return {
    node,
    children,
    loadChildren: fetch,
    moveAfter,
    getCategoryPosition,
    ...rest,
  };
};

export {useCategoryTreeNode};
