import {useEffect, useState} from 'react';
import {JobExecutionSearchTableResult} from '../models/JobExecutionSearchTableResult';
import {useRoute, useIsMounted} from '@akeneo-pim-community/shared';

const useJobExecutionSearchTableResult = (): JobExecutionSearchTableResult | null => {
  const [searchJobResult, setSearchJobResult] = useState<JobExecutionSearchTableResult | null>(null);
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

export {useJobExecutionSearchTableResult};
