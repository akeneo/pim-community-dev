import {useEffect, useState} from 'react';
import {FetchStatus, useFetch, useRoute} from '@akeneo-pim-community/shared';
import {BackendCategoryTree, CategoryTreeModel, convertToCategoryTree} from '../models';

const useCategoryTreeDeprecated = (treeId: number, lastSelectedCategoryId: string) => {
  const [tree, setTree] = useState<CategoryTreeModel | null>(null);
  const [loadingStatus, setLoadingStatus] = useState<FetchStatus>('idle');
  const [error, setError] = useState<string | null>(null);

  const url = useRoute('pim_enrich_categorytree_children', {
    _format: 'json',
    id: treeId.toString(),
    select_node_id: lastSelectedCategoryId,
    with_items_count: '0',
    include_parent: '1',
    include_sub: '0',
    context: 'manage',
  });

  const [data, fetch, fetchStatus, fetchError] = useFetch<BackendCategoryTree>(url);

  useEffect(() => {
    if (fetchStatus === 'fetching' || fetchStatus === 'idle') {
      // Tree is not loaded yet
      return;
    }

    if (fetchStatus === 'error') {
      setLoadingStatus(fetchStatus);
      setError(fetchError);
      setTree(null);
      return;
    }

    if (data === null) {
      setTree(null);
      setLoadingStatus('error');
      setError(`Category tree [${treeId}] not found`);
      return;
    }

    const tree = convertToCategoryTree(data);
    if (!tree.isRoot) {
      setTree(null);
      setLoadingStatus('error');
      setError(`Category tree [${treeId}] not found`);
      return;
    }

    setTree(tree);
    setLoadingStatus(fetchStatus);
    setError(fetchError);
  }, [data, fetchError, fetchStatus, treeId]);

  return {
    tree,
    loadTree: fetch,
    loadingStatus,
    error,
  };
};

export {useCategoryTreeDeprecated};
