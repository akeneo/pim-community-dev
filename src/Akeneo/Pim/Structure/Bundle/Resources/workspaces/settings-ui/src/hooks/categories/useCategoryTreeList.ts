import {useEffect, useState} from 'react';
import {useFetch, useRoute} from '@akeneo-pim-community/shared';
import {CategoryTreeModel} from '../../models';

const useCategoryTreeList = () => {
  const [trees, setTrees] = useState<CategoryTreeModel[]>([]);
  const url = useRoute('pim_enrich_categorytree_listtree', {
    _format: 'json',
    include_sub: '0',
    with_items_count: '0',
  });

  const {data, fetch, status, error} = useFetch<CategoryTreeModel[]>(url);

  const {data: productsNumberByCategory, fetch: fetchProductsNumberByCategory} = useFetch<{
    [categoryId: number]: number;
  }>(useRoute('pim_enrich_categorytree_get_products_number'));

  useEffect(() => {
    setTrees(data || []);
    if (data) {
      (async () => {
        await fetchProductsNumberByCategory();
      })();
    }
  }, [data]);

  useEffect(() => {
    if (productsNumberByCategory) {
      const updatedTrees: CategoryTree[] = trees.map((tree: CategoryTree) => {
        if (productsNumberByCategory.hasOwnProperty(tree.id)) {
          return {
            ...tree,
            productsNumber: productsNumberByCategory[tree.id],
          };
        }

        return tree;
      });
      setTrees(updatedTrees);
    }
  }, [productsNumberByCategory]);

  return {
    trees,
    status,
    load: fetch,
    error,
  };
};

export {useCategoryTreeList};
