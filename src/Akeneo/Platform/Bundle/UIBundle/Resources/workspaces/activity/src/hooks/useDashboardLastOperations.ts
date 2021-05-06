import {useEffect, useState} from 'react';
import {Operation} from '../domain';

const Routing = require('routing');

const useDashboardLastOperations = () => {
  const [data, setData] = useState<Operation[] | null>(null);

  useEffect(() => {
    (async () => {
      const result = await fetch(Routing.generate('pim_dashboard_widget_data', {alias: 'last_operations'}), {
        method: 'GET',
      });
      setData(await result.json());
    })();
  }, []);

  return data;
};

export {useDashboardLastOperations};
