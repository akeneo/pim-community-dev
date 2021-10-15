import {useEffect, useState} from 'react';
import {convertBackendDashboardCompletenessData} from '../helpers';
import {ChannelsLocalesCompletenessRatios} from '@akeneo-pim-community/enrichment';
import {useRouter} from '@akeneo-pim-community/shared';

const useDashboardCompleteness = (catalogLocale: string): ChannelsLocalesCompletenessRatios | null => {
  const [data, setData] = useState<ChannelsLocalesCompletenessRatios | null>(null);
  const router = useRouter();

  useEffect(() => {
    (async () => {
      const result = await fetch(router.generate('pim_dashboard_widget_data', {alias: 'completeness'}), {
        method: 'GET',
      });
      const convertedData = convertBackendDashboardCompletenessData(await result.json(), catalogLocale);
      setData(convertedData);
    })();
  }, [catalogLocale]);

  return data;
};

export {useDashboardCompleteness};
