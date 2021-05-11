import {useCallback, useContext, useEffect, useState} from 'react';
import {CategoryTreeContext, MoveTarget} from '../../components';
import {
  BackendCategoryTree,
  buildTreeNodeFromCategoryTree,
  CategoryTreeModel,
  convertToCategoryTree,
  TreeNode,
} from '../../models';
import {findByIdentifiers, findLoadedDescendantsIdentifiers, findOneByIdentifier, update} from '../../helpers';
import {useFetch, useRoute} from '@akeneo-pim-community/shared';

type Move = {
  identifier: number;
  target: MoveTarget;
  status: 'pending' | 'ready';
  onMove: () => void;
};

const useCategoryTreeNode = (id: number) => {
  const {nodes, setNodes, ...rest} = useContext(CategoryTreeContext);
  const [node, setNode] = useState<TreeNode<CategoryTreeModel> | undefined>(undefined);
  const [children, setChildren] = useState<TreeNode<CategoryTreeModel>[]>([]);
  const [move, setMove] = useState<Move | null>(null);

  const url = useRoute('pim_enrich_categorytree_children', {
    _format: 'json',
    id: id.toString(),
    select_node_id: '-1',
    with_items_count: '0',
    include_parent: '0',
    include_sub: '0',
  });

  const {data, fetch: loadChildren, status: loadChildrenStatus} = useFetch<BackendCategoryTree>(url);

  const getCategoryPosition = (treeNode: TreeNode<CategoryTreeModel>): number => {
    if (!treeNode.parentId) {
      return 0;
    }
    const categoryParent = findOneByIdentifier(nodes, treeNode.parentId);

    return categoryParent?.childrenIds.indexOf(treeNode.identifier) || 0;
  };

  const moveTo = useCallback(
    (movedCategoryId: number, target: MoveTarget, onMove: () => void) => {
      const targetParentNode = findOneByIdentifier(
        nodes,
        target.position === 'in' ? target.identifier : target.parentId
      );
      if (!targetParentNode) {
        console.error(`Node ${target.parentId} not found`);
        // @todo handle error
        return;
      }

      setMove({
        identifier: movedCategoryId,
        target,
        status:
          target.position === 'in' && targetParentNode.type === 'node' && targetParentNode.childrenStatus === 'idle'
            ? 'pending'
            : 'ready',
        onMove,
      });
    },
    [nodes]
  );

  const doMove = useCallback(
    (move: Move) => {
      const {identifier, target, status} = move;
      if (status !== 'ready') {
        return;
      }

      const movedNode = findOneByIdentifier(nodes, identifier);
      if (!movedNode) {
        console.error(`Node ${identifier} not found`);
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
        const updateOriginalParentChildren = originalParentNode.childrenIds.filter(id => id !== movedNode.identifier);
        newNodesList = update(newNodesList, {
          ...originalParentNode,
          childrenIds: updateOriginalParentChildren,
          type: originalParentNode.type !== 'root' ? (updateOriginalParentChildren.length > 0 ? 'node' : 'leaf') : 'root',
        });
      }

      setNodes(newNodesList);

      // call callback to save it in backend
      // what we have to do if the callback fails? keep original position

      move.onMove();
      setMove(null);
    },
    [nodes]
  );

  // When a category is deleted, update its parent's node and remove the category's node and its descendants.
  const onDeleteCategory = () => {
    if (!node || !node.parentId) {
      return;
    }

    const nodesToRemove = [node.identifier, ...findLoadedDescendantsIdentifiers(nodes, node)];
    const updatedNodes = nodes.filter(treeNode => !nodesToRemove.includes(treeNode.identifier));

    const parentNode = findOneByIdentifier(nodes, node.parentId);
    if (!parentNode) {
      setNodes(updatedNodes);
      return;
    }

    const updatedParentChildren = parentNode.childrenIds.filter(childId => childId !== node.identifier);
    const updatedParent: TreeNode<CategoryTreeModel> = {
      ...parentNode,
      childrenIds: updatedParentChildren,
      type: parentNode.type !== 'root' ? (updatedParentChildren.length > 0 ? 'node' : 'leaf') : 'root',
    };

    setNodes(update(updatedNodes, updatedParent));
  };

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
  }, [node?.childrenStatus]);

  useEffect(() => {
    if (move === null) {
      return;
    }

    if (move.status === 'pending') {
      const loadChildrenBeforeMove = async () => {
        await loadChildren();

        setMove({
          ...move,
          status: 'ready',
        });
      };
      loadChildrenBeforeMove();

      return;
    }

    if (move.status === 'ready') {
      doMove(move);
      return;
    }
  }, [move]);

  useEffect(() => {
    if (!node || loadChildrenStatus !== 'fetching') {
      return;
    }
    const newNodesList = update(nodes, {
      ...node,
      childrenStatus: 'loading',
    });

    setNodes(newNodesList);
  }, [loadChildrenStatus]);

  return {
    node,
    children,
    loadChildren,
    forceReloadChildren,
    moveTo,
    getCategoryPosition,
    onDeleteCategory,
    ...rest,
  };
};

export {useCategoryTreeNode};
