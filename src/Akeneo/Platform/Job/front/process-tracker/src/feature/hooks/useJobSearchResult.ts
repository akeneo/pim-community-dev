import {useEffect, useState} from 'react';
import {JobSearchResult} from '../models/JobSearchResult';
import {useRoute, useIsMounted} from '@akeneo-pim-community/shared';

const useJobSearchResult = (): JobSearchResult | null => {
  const [searchJobResult, setSearchJobResult] = useState<JobSearchResult | null>(null);
  const route = useRoute('akeneo_job_index_action');
  const isMounted = useIsMounted();

  useEffect(() => {
    const searchJob = async () => {
      const response = await fetch(route, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
        },
      });

      if (isMounted()) {
        setSearchJobResult(await response.json());
      }
    };

    searchJob();
  }, [route, isMounted, setSearchJobResult]);

  return searchJobResult;
};

export {useJobSearchResult};
