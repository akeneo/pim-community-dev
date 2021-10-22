import {useRouter} from '@akeneo-pim-community/shared';
import {useEffect, useState} from 'react';
import {Operation} from '../domain';

const useDashboardLastOperations = () => {
  const [data, setData] = useState<Operation[] | null>(null);
  const router = useRouter();

  useEffect(() => {
    (async () => {
      const result = await fetch(router.generate('pim_dashboard_widget_data', {alias: 'last_operations'}), {
        method: 'GET',
      });
      setData(await result.json());
    })();
  }, []);

  return data;
};

export {useDashboardLastOperations};
