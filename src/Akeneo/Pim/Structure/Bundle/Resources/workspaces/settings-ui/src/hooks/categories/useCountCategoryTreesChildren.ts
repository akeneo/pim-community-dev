import {useEffect, useState} from 'react';
import {baseFetcher, useRoute} from '@akeneo-pim-community/shared';

type CountCategoryTreesChildren = {
  [key: string]: number;
};

const useCountCategoryTreesChildren = (): CountCategoryTreesChildren => {
  const [countChildren, setCountChildren] = useState<CountCategoryTreesChildren>({});
  const url = useRoute('pim_enrich_categorytree_count_children');

  useEffect(() => {
    (async () => {
      setCountChildren(await baseFetcher(url));
    })();
  }, []);

  return countChildren;
};

export {useCountCategoryTreesChildren};
