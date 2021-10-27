import {useEffect, useState} from 'react';
import {SearchJobExecutionTableResult} from '../models/SearchJobExecutionTableResult';
import {useRoute, useIsMounted} from '@akeneo-pim-community/shared';

const useSearchJobExecutionTableResult = (): SearchJobExecutionTableResult | null => {
  const [searchJobExecutionTableResult, setSearchJobExecutionTableResult] =
    useState<SearchJobExecutionTableResult | null>(null);
  const route = useRoute('akeneo_job_index_action');
  const isMounted = useIsMounted();

  useEffect(() => {
    const searchJobExecution = async () => {
      const response = await fetch(route, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
        },
      });

      if (isMounted()) {
        setSearchJobExecutionTableResult(await response.json());
      }
    };

    searchJobExecution();
  }, [route, isMounted, setSearchJobExecutionTableResult]);

  return searchJobExecutionTableResult;
};

export {useSearchJobExecutionTableResult};
