import {useEffect, useState} from 'react';
import {useRoute} from '@akeneo-pim-community/legacy-bridge';
import {FetchStatus, useFetch} from '@akeneo-pim-community/shared';
import {BackendCategoryTree, CategoryTree, convertToCategoryTree} from '../../models';

const useCategoryTree = (treeId: number) => {
  const [tree, setTree] = useState<CategoryTree | null>(null);
  const [status, setStatus] = useState<FetchStatus>('idle');
  const [error, setError] = useState<string | null>(null);

  const url = useRoute('pim_enrich_categorytree_children', {
    _format: 'json',
    id: treeId.toString(),
    select_node_id: '-1',
    with_items_count: '0',
    include_parent: '1',
    include_sub: '0',
  });

  const {data, fetch, error: fetchError, status: fetchStatus} = useFetch<BackendCategoryTree>(url);

  useEffect(() => {
    if (fetchStatus === 'error') {
      setStatus(fetchStatus);
      setError(fetchError);
      setTree(null);
      return;
    }

    if (data === null && fetchStatus === 'fetched') {
      setTree(null);
      setStatus('error');
      setError(`Category tree [${treeId}] not found`);
    }

    if (data === null) {
      // Tree is not loaded yet
      return;
    }

    const tree = convertToCategoryTree(data);
    if (!tree.isRoot) {
      setTree(null);
      setStatus('error');
      setError(`Category tree [${treeId}] not found`);
      return;
    }

    setTree(tree);
    setStatus(fetchStatus);
    setError(fetchError);
  }, [data, fetchError, fetchStatus]);

  return {
    tree,
    status,
    error,
    load: fetch,
  };
};

export {useCategoryTree};
