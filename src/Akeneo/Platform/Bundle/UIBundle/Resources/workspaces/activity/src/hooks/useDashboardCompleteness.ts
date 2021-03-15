import {useEffect, useState} from 'react';
import {convertBackendDashboardCompletenessData} from '../helpers';
import {ChannelsLocalesCompletenesses} from '../domain';

const Routing = require('routing');

const useDashboardCompleteness = (catalogLocale: string): ChannelsLocalesCompletenesses | null => {
  const [data, setData] = useState<ChannelsLocalesCompletenesses | null>(null);

  useEffect(() => {
    (async () => {
      const result = await fetch(Routing.generate('pim_dashboard_widget_data', {alias: 'completeness'}), {
        method: 'GET',
      });
      const convertedData = convertBackendDashboardCompletenessData(await result.json(), catalogLocale);
      setData(convertedData);
    })();
  }, [catalogLocale]);

  return data;
};

export {useDashboardCompleteness};
