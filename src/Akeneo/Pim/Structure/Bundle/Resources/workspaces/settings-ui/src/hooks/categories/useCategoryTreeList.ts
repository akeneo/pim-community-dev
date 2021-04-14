import {useCallback, useState} from 'react';
import {Category} from '../../models';
import {useRoute} from '@akeneo-pim-community/legacy-bridge';

const useCategoryTreeList = () => {
  const [trees, setTrees] = useState<Category[]>([]);
  const [isPending, setIsPending] = useState(false);
  const url = useRoute('pim_enrich_categorytree_listtree', {
    _format: 'json',
    include_sub: '0',
    with_items_count: '0',
  });

  const load = useCallback(async () => {
    setIsPending(true);

    try {
      const response = await fetch(url);
      const data: Category[] = await response.json();
      setTrees(data);
      setIsPending(false);
    } catch (e) {
      console.error(e.message);
      setTrees([]);
      setIsPending(false);
    }
  }, [url]);

  return {
    trees,
    isPending,
    load,
  };
};

export {useCategoryTreeList};
