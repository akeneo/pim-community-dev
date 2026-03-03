import {useEffect, useState} from 'react';
import {useRoute} from '@akeneo-pim-community/shared';

type CountCategoryTreesChildren = {
  [key: string]: number;
};

const useCountCategoryTreesChildren = (): CountCategoryTreesChildren | null => {
  const [countChildren, setCountChildren] = useState<CountCategoryTreesChildren | null>(null);
  const url = useRoute('pim_enrich_categorytree_count_trees_children');

  useEffect(() => {
    (async () => {
      const response = await fetch(url);
      setCountChildren(await response.json());
    })();
  }, [url]);

  return countChildren;
};

export {useCountCategoryTreesChildren};
