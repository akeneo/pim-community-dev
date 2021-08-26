import {useState, useCallback, useEffect} from 'react';
import {useRoute} from '@akeneo-pim-community/shared';
import {validatePimAnalyticsData} from '../validator/pimAnalyticsData';

type PimVersion = {
  edition: string;
  version: string;
};

const usePimVersion = (): {
  data: PimVersion | null;
  hasError: boolean;
} => {
  const [pimVersion, setPimVersion] = useState<{
    data: PimVersion | null;
    hasError: boolean;
  }>({
    data: null,
    hasError: false,
  });
  const route = useRoute('pim_analytics_data_collect');

  const updatePimVersion = useCallback(async () => {
    try {
      const response = await fetch(route);
      const data = await response.json();
      validatePimAnalyticsData(data);

      setPimVersion({
        data: {edition: data.pim_edition, version: data.pim_version},
        hasError: false,
      });
    } catch (error) {
      setPimVersion({data: null, hasError: true});
    }
  }, [setPimVersion, route]);

  useEffect(() => {
    updatePimVersion();
  }, []);

  return pimVersion;
};

export {usePimVersion};
