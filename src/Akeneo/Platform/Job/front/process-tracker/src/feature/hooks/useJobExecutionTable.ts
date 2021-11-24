import {useCallback, useEffect, useState} from 'react';
import {useRoute} from '@akeneo-pim-community/shared';
import {JobExecutionFilter, JobExecutionTable} from '../models';

const useJobExecutionTable = ({page, size, sort, type, status, users, search}: JobExecutionFilter) => {
  const [jobExecutionTable, setJobExecutionTable] = useState<JobExecutionTable | null>(null);
  const route = useRoute('akeneo_job_index_action');

  const searchJobExecution = useCallback(async () => {
    const response = await fetch(route, {
      body: JSON.stringify({
        page: page.toString(),
        size: size.toString(),
        sort,
        status,
        type,
        users,
        search,
      }),
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      method: 'POST',
    });

    setJobExecutionTable(await response.json());
  }, [route, page, size, sort, type, status, users, search]);

  useEffect(() => {
    searchJobExecution();
  }, [searchJobExecution]);

  return [jobExecutionTable, searchJobExecution] as const;
};

export {useJobExecutionTable};
