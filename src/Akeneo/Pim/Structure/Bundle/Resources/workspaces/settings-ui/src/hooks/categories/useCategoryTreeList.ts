import {useEffect, useState} from 'react';
import {useFetch, useRoute} from '@akeneo-pim-community/shared';
import {CategoryTree} from '../../models';

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
