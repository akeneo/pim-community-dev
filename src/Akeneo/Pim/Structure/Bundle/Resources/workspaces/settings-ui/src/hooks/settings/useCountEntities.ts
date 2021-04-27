import {useEffect, useState} from 'react';
import {baseFetcher, useRoute} from '@akeneo-pim-community/shared';

type CountEntities = {
  [key: string]: number;
};

const useCountEntities = (): CountEntities => {
  const [countEntities, setCountEntities] = useState<CountEntities>({});
  const url = useRoute('pim_enrich_count_settings_entities');

  useEffect(() => {
    (async () => {
      setCountEntities(await baseFetcher(url));
    })();
  }, []);

  return countEntities;
};

export {useCountEntities};
