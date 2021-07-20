import React, {useEffect, useState} from 'react';
import {useRoute, useIsMounted, Category} from '@akeneo-pim-community/shared';

type CategoryTree = Category & {
  selectedCategoryCount: number;
};

const useCategoryTrees = (
  selectedCategoryCodes: string[],
  shouldIncludeChildren: boolean,
  setActiveCategoryTree?: React.Dispatch<React.SetStateAction<string>>
) => {
  const [categoryTrees, setCategoryTrees] = useState<CategoryTree[]>([]);
  const isMounted = useIsMounted();
  const route = useRoute('pim_importexport_category_tree_list');

  useEffect(() => {
    const fetchCategories = async () => {
      const response = await fetch(route, {
        method: 'POST',
        headers: [
          ['Content-type', 'application/json'],
          ['X-Requested-With', 'XMLHttpRequest'],
        ],
        body: JSON.stringify({selectedCategoryCodes, shouldIncludeChildren}),
      });
      const json = await response.json();

      if (isMounted()) {
        setCategoryTrees(json);
        setActiveCategoryTree?.(currentActiveCategoryTree =>
          '' === currentActiveCategoryTree ? json[0].code : currentActiveCategoryTree
        );
      }
    };

    fetchCategories();
  }, [route, setCategoryTrees, shouldIncludeChildren, isMounted, selectedCategoryCodes, setActiveCategoryTree]);

  return categoryTrees;
};

export {useCategoryTrees};
