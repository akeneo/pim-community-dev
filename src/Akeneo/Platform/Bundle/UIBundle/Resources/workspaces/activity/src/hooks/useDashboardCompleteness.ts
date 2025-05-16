import {useEffect, useState} from 'react';
import {convertBackendDashboardCompletenessData} from '../helpers';
import {ChannelsLocalesCompletenessRatios} from '@akeneo-pim-community/enrichment';
import {useRouter} from '@akeneo-pim-community/shared';

const useDashboardCompleteness = (catalogLocale: string): ChannelsLocalesCompletenessRatios | null => {
  const [data, setData] = useState<ChannelsLocalesCompletenessRatios | null>(null);
  const router = useRouter();

  useEffect(() => {
    (async () => {
      try {
        const result = await fetch(router.generate('pim_dashboard_widget_data', {alias: 'completeness'}), {
          method: 'GET',
        });
        const data = await result.json();
        const convertedData = convertBackendDashboardCompletenessData(data, catalogLocale);
        setData(convertedData);
      } catch (error) {
        setData(null);
      }
    })();
  }, [catalogLocale]);

  return data;
};

export {useDashboardCompleteness};
