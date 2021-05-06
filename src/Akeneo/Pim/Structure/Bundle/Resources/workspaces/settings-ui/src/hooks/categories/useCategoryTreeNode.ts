import {useCallback, useContext, useEffect, useState} from 'react';
import {CategoryTreeContext, MoveTarget} from '../../components';
import {
  BackendCategoryTree,
  buildTreeNodeFromCategoryTree,
  CategoryTreeModel,
  convertToCategoryTree,
  TreeNode,
} from '../../models';
import {findByIdentifiers, findOneByIdentifier, update} from '../../helpers';
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

  const {data, fetch: loadChildren, error: fetchError, status: fetchStatus} = useFetch<BackendCategoryTree>(url);

  const getCategoryPosition = (treeNode: TreeNode<CategoryTreeModel>): number => {
    if (!treeNode.parentId) {
      return 0;
    }
    const categoryParent = findOneByIdentifier(nodes, treeNode.parentId);

    return categoryParent?.childrenIds.indexOf(treeNode.identifier) || 0;
  };

  const moveTo = useCallback(
    (movedCategoryId: number, target: MoveTarget) => {
      if (!target.parentId) {
        console.error('Can not move after root node');
        // @todo handle error
        return;
      }

      const movedNode = findOneByIdentifier(nodes, movedCategoryId);
      if (!movedNode) {
        console.error(`Node ${movedCategoryId} not found`);
        // @todo handle error
        return;
      }

      const targetParentNode = findOneByIdentifier(
        nodes,
        target.position === 'in' ? target.identifier : target.parentId
      );
      if (!targetParentNode) {
        console.error(`Node ${target.parentId} not found`);
        // @todo handle error
        return;
      }

      if (!movedNode.parentId) {
        console.error('Can not move root node');
        // @todo handle error
        return;
      }
      const originalParentNode = findOneByIdentifier(nodes, movedNode.parentId);
      if (!originalParentNode) {
        console.error(`Node ${movedNode.parentId} not found`);
        // @todo handle error
        return;
      }

      let newNodesList = nodes;

      // update the children of parent
      // We ensure that the moved node is not in the list
      const parentChildrenIds = targetParentNode.childrenIds.filter(id => id !== movedNode.identifier);

      // console.log(targetParentNode.children, parentChildrenIds);
      const movedIndex = parentChildrenIds.findIndex(id => id === target.identifier);

      parentChildrenIds.splice(
        target.position === 'in' ? 0 : target.position === 'after' ? movedIndex + 1 : movedIndex,
        0,
        movedNode.identifier
      );

      newNodesList = update(newNodesList, {
        ...targetParentNode,
        childrenIds: parentChildrenIds,
        type: targetParentNode.type === 'leaf' ? 'node' : targetParentNode.type,
        childrenStatus: 'loaded',
      });

      // update parent id for the original node
      newNodesList = update(newNodesList, {
        ...movedNode,
        parentId: targetParentNode.identifier,
      });

      // remove the original id from the original parent's children
      // update the original parent
      if (originalParentNode.identifier !== targetParentNode.identifier) {
        newNodesList = update(newNodesList, {
          ...originalParentNode,
          childrenIds: originalParentNode.childrenIds.filter(id => id !== movedNode.identifier),
        });
      }

      setNodes(newNodesList);

      // call callback to save it in backend
      // what we have to do if the callback fails? keep original position
    },
    [nodes]
  );

  const forceReloadChildren = useCallback(() => {
    if (node) {
      setNode({...node, childrenStatus: 'to-reload'});
    }
  }, [node]);

  useEffect(() => {
    setNode(findOneByIdentifier(nodes, id));
  }, [id, nodes]);

  useEffect(() => {
    if (!node) {
      return;
    }
    setChildren(findByIdentifiers(nodes, node.childrenIds));
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
      childrenIds: newChildren.map(child => child.identifier),
      childrenStatus: 'loaded',
      type: node.type !== 'root' ? (newChildren.length > 0 ? 'node' : 'leaf') : 'root',
    });

    // @todo check uniqueness of new children
    setNodes([...updatedNodes, ...newChildren]);
  }, [data]);

  useEffect(() => {
    if (node?.childrenStatus === 'to-reload') {
      loadChildren();
    }
  }, [node?.childrenStatus])

  return {
    node,
    children,
    loadChildren,
    forceReloadChildren,
    moveTo,
    getCategoryPosition,
    ...rest,
  };
};

export {useCategoryTreeNode};
