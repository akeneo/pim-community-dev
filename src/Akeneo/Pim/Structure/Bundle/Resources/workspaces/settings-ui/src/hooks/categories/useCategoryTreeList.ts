import {useEffect, useState} from 'react';
import {useRoute} from '@akeneo-pim-community/legacy-bridge';
import {CategoryTree} from '../../models';
import {useFetch} from '@akeneo-pim-community/shared';

const useCategoryTreeList = () => {
  const [trees, setTrees] = useState<CategoryTree[]>([]);
  const url = useRoute('pim_enrich_categorytree_listtree', {
    _format: 'json',
    include_sub: '0',
    with_items_count: '0',
  });

  const {data, fetch, status, error} = useFetch<CategoryTree[]>(url);

  useEffect(() => {
    setTrees(data || []);
  }, [data]);

  return {
    trees,
    status,
    load: fetch,
    error,
  };
};

export {useCategoryTreeList};
