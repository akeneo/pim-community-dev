import {useState, useCallback, useEffect} from 'react';
import {useRoute} from '../legacy-bridge/src/hooks';
import {baseFetcher} from '../shared/src/fetcher';

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
      const data = await baseFetcher(route);

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
