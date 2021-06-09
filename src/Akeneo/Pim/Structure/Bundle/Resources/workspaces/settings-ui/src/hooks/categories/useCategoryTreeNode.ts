import {useCallback, useContext, useEffect, useMemo, useState} from 'react';
import {CategoryTreeContext} from '../../components';
import {
  BackendCategoryTree,
  buildTreeNodeFromCategoryTree,
  CategoryTreeModel,
  convertToCategoryTree,
  DropTarget,
  TreeNode,
} from '../../models';
import {findByIdentifiers, findLoadedDescendantsIdentifiers, findOneByIdentifier, update} from '../../helpers';
import {useFetch, useRoute} from '@akeneo-pim-community/shared';
import {moveCategory} from '../../infrastructure/savers';
import {useBooleanState} from 'akeneo-design-system';

type Move = {
  identifier: number;
  target: DropTarget;
  status: 'pending' | 'ready';
  onMove: () => void;
};

const useCategoryTreeNode = (id: number) => {
  const {nodes, setNodes, ...rest} = useContext(CategoryTreeContext);
  const [move, setMove] = useState<Move | null>(null);
  const [isOpen, open, close] = useBooleanState(false);

  const url = useRoute('pim_enrich_categorytree_children', {
    _format: 'json',
    id: id.toString(),
    select_node_id: '-1',
    with_items_count: '0',
    include_parent: '0',
    include_sub: '0',
  });

  const {data, fetch: loadChildren, status: loadChildrenStatus} = useFetch<BackendCategoryTree>(url);

  const node = useMemo(() => findOneByIdentifier(nodes, id), [id, nodes]);
  const children = useMemo(() => (!node ? [] : findByIdentifiers(nodes, node.childrenIds)), [node, nodes]);

  const getCategoryPosition = (treeNode: TreeNode<CategoryTreeModel>): number => {
    if (!treeNode.parentId) {
      return 0;
    }
    const categoryParent = findOneByIdentifier(nodes, treeNode.parentId);

    return categoryParent?.childrenIds.indexOf(treeNode.identifier) || 0;
  };

  const moveTo = useCallback(
    (movedCategoryId: number, target: DropTarget, onMove: () => void) => {
      const targetParentNode = findOneByIdentifier(
        nodes,
        target.position === 'in' ? target.identifier : target.parentId
      );
      if (!targetParentNode) {
        console.error(`Parent node ${target.parentId} not found to set the move of node ${target.identifier}`);
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
    async (move: Move) => {
      const {identifier, target, status} = move;
      if (status !== 'ready') {
        return;
      }

      if (identifier === target.identifier) {
        return;
      }

      const movedNode = findOneByIdentifier(nodes, identifier);
      if (!movedNode) {
        console.error(`Failed to move node ${identifier} : Node not found`);
        return;
      }

      const targetParentNode = findOneByIdentifier(
        nodes,
        target.position === 'in' ? target.identifier : target.parentId
      );
      if (!targetParentNode) {
        console.error(`Failed to move node ${identifier} : Target parent node ${target.parentId} not found`);
        return;
      }

      if (!movedNode.parentId) {
        console.error(`Failed to move node ${identifier} : Can not move root node`);
        return;
      }
      const originalParentNode = findOneByIdentifier(nodes, movedNode.parentId);
      if (!originalParentNode) {
        console.error(`Failed to move node ${identifier} : Moved parent Node ${movedNode.parentId} not found`);
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
          type:
            originalParentNode.type !== 'root' ? (updateOriginalParentChildren.length > 0 ? 'node' : 'leaf') : 'root',
        });
      }

      setNodes(newNodesList);

      // Call to backend to persist the movement
      const persistSuccess = await moveCategory({
        identifier,
        parentId: target.position === 'in' ? target.identifier : target.parentId,
        previousCategoryId: determineAfterWhichCategoryIdentifierToMove(target, targetParentNode.childrenIds),
      });

      if (!persistSuccess) {
        console.error(`Failed to persist node ${identifier}`);
      }
    },
    [nodes]
  );

  const determineAfterWhichCategoryIdentifierToMove = (target: DropTarget, childrenIds: number[]): number | null => {
    if (target.position === 'after') {
      return target.identifier;
    }

    if (target.position === 'before') {
      const targetIndex = childrenIds.indexOf(target.identifier) - 1;
      if (targetIndex >= 0 && childrenIds[targetIndex]) {
        return childrenIds[targetIndex];
      }
    }

    return null;
  };

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

  // When a category is created inside the node, force reload children and open it.
  const onCreateCategory = useCallback(() => {
    if (node) {
      const updatedNodes = update(nodes, {
        ...node,
        childrenStatus: 'to-reload',
      });

      setNodes([...updatedNodes]);
      open();
    }
  }, [node]);

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
      move.onMove();
      setMove(null);

      return;
    }
  }, [move]);

  useEffect(() => {
    if (!node) {
      return;
    }

    if (loadChildrenStatus === 'error') {
      const newNodesList = update(nodes, {
        ...node,
        childrenStatus: 'idle',
      });

      setNodes(newNodesList);
    }

    if (loadChildrenStatus === 'fetching') {
      const newNodesList = update(nodes, {
        ...node,
        childrenStatus: 'loading',
      });

      setNodes(newNodesList);
    }
  }, [loadChildrenStatus]);

  return {
    node,
    children,
    loadChildren,
    moveTo,
    getCategoryPosition,
    onDeleteCategory,
    onCreateCategory,
    isOpen,
    open,
    close,
    ...rest,
  };
};

export {useCategoryTreeNode};
