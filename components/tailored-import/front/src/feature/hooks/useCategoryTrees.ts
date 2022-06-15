import {useEffect, useState} from 'react';
import {CategoryTree} from '../models/Category';
import {useRoute} from '@akeneo-pim-community/shared';

const useCategoryTrees = (): CategoryTree[] => {
  const [categoryTrees, setCategoryTrees] = useState<CategoryTree[]>([]);
  const categoryTreesRoute = useRoute('pimee_tailored_import_get_category_trees_action');

  useEffect(() => {
    const fetchCategoryTrees = async () => {
      const response = await fetch(categoryTreesRoute, {
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      });

      const result = await response.json();

      setCategoryTrees(result);
    };

    fetchCategoryTrees();
  }, [categoryTreesRoute]);

  return categoryTrees;
};

export {useCategoryTrees};
