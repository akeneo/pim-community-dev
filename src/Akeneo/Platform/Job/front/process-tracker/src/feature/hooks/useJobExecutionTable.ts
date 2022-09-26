import {useCallback, useEffect, useRef, useState} from 'react';
import {useRoute, useIsMounted, useDocumentVisibility} from '@akeneo-pim-community/shared';
import {JobExecutionFilter, JobExecutionTable} from '../models';

const AUTO_REFRESH_FREQUENCY = 5000;

const useJobExecutionTable = (
  {page, size, sort, automation, type, status, code, user, search}: JobExecutionFilter,
  autoRefresh = true
) => {
  const [jobExecutionTable, setJobExecutionTable] = useState<JobExecutionTable | null>(null);
  const route = useRoute('akeneo_job_index_action');
  const isMounted = useIsMounted();
  const isDocumentVisible = useDocumentVisibility();
  const isFetching = useRef<boolean>(false);

  const searchJobExecution = useCallback(async () => {
    if (isFetching.current) return;

    isFetching.current = true;
    const response = await fetch(route, {
      body: JSON.stringify({
        page: page.toString(),
        size: size.toString(),
        sort,
        status,
        automation,
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

    isFetching.current = false;
    const jobExecutionTable = await response.json();
    if (isMounted()) {
      setJobExecutionTable(jobExecutionTable);
    }
  }, [isMounted, route, page, size, sort, automation, type, status, search, user, code]);

  useEffect(() => {
    void searchJobExecution();
  }, [searchJobExecution]);

  useEffect(() => {
    if (!isDocumentVisible || !autoRefresh) return;

    const interval = setInterval(() => {
      void searchJobExecution();
    }, AUTO_REFRESH_FREQUENCY);

    return () => {
      clearInterval(interval);
    };
  }, [isDocumentVisible, searchJobExecution, page, size, sort, type, status, search, user, code, autoRefresh]);

  return [jobExecutionTable, searchJobExecution] as const;
};

export {useJobExecutionTable};
