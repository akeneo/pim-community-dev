import {useCallback, useEffect, useState} from 'react';
import {useRoute, useIsMounted, useDocumentVisibility} from '@akeneo-pim-community/shared';
import {JobExecutionFilter, JobExecutionTable} from '../models';

const AUTO_REFRESH_FREQUENCY = 5000;

const useJobExecutionTable = ({page, size, sort, type, status, code, user, search}: JobExecutionFilter) => {
  const [jobExecutionTable, setJobExecutionTable] = useState<JobExecutionTable | null>(null);
  const route = useRoute('akeneo_job_index_action');
  const isMounted = useIsMounted();
  const isDocumentVisible = useDocumentVisibility();
  const [isFetching, setIsFetching] = useState<boolean>(false);

  const searchJobExecution = useCallback(async () => {
    console.log('is fetching', isFetching);
    if (isFetching) return;

    console.log('is mounted', isMounted());

    if (isMounted()) {
      setIsFetching(true);

      const response = await fetch(route, {
        body: JSON.stringify({
          page: page.toString(),
          size: size.toString(),
          sort,
          status,
          type,
          user,
          search,
          code,
        }),
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        method: 'POST',
      });

      setIsFetching(false);
      setJobExecutionTable(await response.json());
    }
  }, [isMounted, isFetching, route, page, size, sort, type, status, search, user, code]);

  useEffect(() => {
    searchJobExecution();
  }, [searchJobExecution]);

  // useEffect(() => {
  //   if (!isDocumentVisible) {
  //     return;
  //   }
  //
  //   const interval = setInterval(() => {
  //     searchJobExecution();
  //   }, AUTO_REFRESH_FREQUENCY);
  //
  //   return () => {
  //     clearInterval(interval);
  //   };
  // }, [isDocumentVisible, isFetching, searchJobExecution, page, size, sort, type, status, search, user, code]);

  return [jobExecutionTable, searchJobExecution] as const;
};

export {useJobExecutionTable};
