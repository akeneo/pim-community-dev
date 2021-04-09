import {useCallback, useState} from 'react';
import {BackendCategoryTree, CategoryTree, convertToCategoryTree} from '../../models';
import {useRoute} from '@akeneo-pim-community/legacy-bridge';

export type FetchStatus = 'idle' | 'error' | 'fetching' | 'fetched';

const useCategoryTree = (treeId: string) => {
  const [tree, setTree] = useState<CategoryTree | null>(null);
  const [status, setStatus] = useState<FetchStatus>('idle');
  const [error, setError] = useState<string|null>(null);

  const url = useRoute('pim_enrich_categorytree_children', {
    _format: 'json',
    id: treeId,
    select_node_id: '-1',
    with_items_count: '0',
    include_parent: '1',
    include_sub: '0',
  });

  const load = useCallback(async () => {
    setStatus('fetching');

    try {
      const response = await fetch(url);
      const data: BackendCategoryTree = await response.json();

      const tree = convertToCategoryTree(data);

      if (!tree.isRoot) {
        setTree(null);
        setStatus('error');
        setError(`Category tree [${treeId}] not found`);
        return;
      }

      setTree(tree);
      setStatus('fetched');
    } catch (e) {
      setTree(null);
      setStatus('error');
      setError(e.message);
    }
  }, [url]);

  return {
    tree,
    status,
    error,
    load,
  };
};

export {useCategoryTree};
