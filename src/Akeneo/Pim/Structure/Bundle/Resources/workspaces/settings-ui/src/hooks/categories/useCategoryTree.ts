import {useCallback, useState} from 'react';
import {BackendCategoryTree, CategoryTree, convertToCategoryTree} from '../../models';
import {useRoute} from '@akeneo-pim-community/legacy-bridge';

const useCategoryTree = (treeId: string, root: boolean = false) => {
  const [tree, setTree] = useState<CategoryTree | null>(null);
  const [isPending, setIsPending] = useState(false);
  const url = useRoute('pim_enrich_categorytree_children', {
    _format: 'json',
    id: treeId,
    select_node_id: '-1',
    with_items_count: '0',
    include_parent: '1',
    include_sub: '0',
  });

  const load = useCallback(async () => {
    setIsPending(true);

    try {
      const response = await fetch(url);
      const data: BackendCategoryTree = await response.json();

      const tree = convertToCategoryTree(data);

      if (root && !tree.isRoot) {
        setTree(null);
        setIsPending(false);
        return;
      }

      setTree(tree);
      setIsPending(false);
    } catch (e) {
      console.error(e.message);
      setTree(null);
      setIsPending(false);
    }
  }, [url]);

  return {
    tree,
    isPending,
    load,
  };
};

export {useCategoryTree};
