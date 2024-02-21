import {useEffect, useState} from 'react';
import {useRoute} from '@akeneo-pim-community/shared';

type CountEntities = {
  [key: string]: number;
};

const useCountEntities = (): CountEntities => {
  const [countEntities, setCountEntities] = useState<CountEntities>({});
  const url = useRoute('pim_system_count_entities');

  useEffect(() => {
    (async () => {
      const response = await fetch(url);
      setCountEntities(await response.json());
    })();
  }, []);

  return countEntities;
};

export {useCountEntities, CountEntities};
