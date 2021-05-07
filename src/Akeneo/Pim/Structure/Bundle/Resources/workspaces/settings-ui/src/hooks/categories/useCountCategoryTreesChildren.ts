import {useEffect, useState} from 'react';
import {baseFetcher, useRoute} from '@akeneo-pim-community/shared';

type CountCategoryTreesChildren = {
  [key: string]: number;
};

const useCountCategoryTreesChildren = (): CountCategoryTreesChildren | null => {
  const [countChildren, setCountChildren] = useState<CountCategoryTreesChildren | null>(null);
  const url = useRoute('pim_enrich_categorytree_count_children');

  useEffect(() => {
    (async () => {
      setCountChildren(await baseFetcher(url));
    })();
  }, []);

  return countChildren;
};

export {useCountCategoryTreesChildren};
