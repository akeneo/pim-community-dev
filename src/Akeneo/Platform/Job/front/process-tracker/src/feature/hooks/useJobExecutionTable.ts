import {useCallback, useEffect, useState} from 'react';
import {useRoute, useIsMounted} from '@akeneo-pim-community/shared';
import {JobExecutionFilter, JobExecutionTable} from '../models';

const useJobExecutionTable = ({page, size, sort, type, status, code, user, search}: JobExecutionFilter) => {
  const [jobExecutionTable, setJobExecutionTable] = useState<JobExecutionTable | null>(null);
  const route = useRoute('akeneo_job_index_action');
  const isMounted = useIsMounted();

  const searchJobExecution = useCallback(async () => {
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

    if (isMounted()) {
      setJobExecutionTable(await response.json());
    }
  }, [isMounted, route, page, size, sort, type, status, search, user, code]);

  useEffect(() => {
    searchJobExecution();
  }, [searchJobExecution]);

  return [jobExecutionTable, searchJobExecution] as const;
};

export {useJobExecutionTable};
