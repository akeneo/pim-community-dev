import {useState, useCallback, useEffect} from 'react';
import {useRoute} from '@akeneo-pim-community/legacy-bridge';
import {baseFetcher} from '@akeneo-pim-community/shared';
import {validatePimAnalyticsData} from '../validator/pimAnalyticsData';

type PimVersion = {
  edition: string;
  version: string;
};

const usePimVersion = (): {pimVersion: PimVersion | null; updatePimVersion: () => Promise<void>} => {
  const [pimVersion, setPimVersion] = useState<PimVersion | null>(null);
  const route = useRoute('pim_analytics_data_collect');

  const updatePimVersion = useCallback(async () => {
    const data = await baseFetcher(route);

    try {
      validatePimAnalyticsData(data);
    } catch (error) {
      setPimVersion(null);
    }

    setPimVersion({edition: data.pim_edition, version: data.pim_version});
  }, [setPimVersion, route]);

  useEffect(() => {
    updatePimVersion();
  }, []);

  return {pimVersion, updatePimVersion};
};

export {usePimVersion};
