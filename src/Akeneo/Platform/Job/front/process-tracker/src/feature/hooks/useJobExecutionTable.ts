import {useEffect, useState} from 'react';
import {useRoute, useIsMounted} from '@akeneo-pim-community/shared';
import {JobExecutionFilter, JobExecutionTable} from '../models';

const useJobExecutionTable = ({page, size, sort, type, status}: JobExecutionFilter): JobExecutionTable | null => {
  const [jobExecutionTable, setJobExecutionTable] = useState<JobExecutionTable | null>(null);
  const route = useRoute('akeneo_job_index_action');
  const isMounted = useIsMounted();

  useEffect(() => {
    const searchJobExecution = async () => {
      const response = await fetch(route, {
        body: JSON.stringify({
          page: page.toString(),
          size: size.toString(),
          sort_column: sort.column,
          sort_direction: sort.direction,
          status,
          type,
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
    };

    searchJobExecution();
  }, [isMounted, route, page, size, sort, type, status]);

  return jobExecutionTable;
};

export {useJobExecutionTable};
