import {useEffect, useState} from 'react';
import {useRoute, useIsMounted} from '@akeneo-pim-community/shared';
import {JobExecutionTable, JobStatus} from '../models';

const useJobExecutionTable = (page: number, size: number, status: JobStatus[]): JobExecutionTable | null => {
  const [jobExecutionTable, setJobExecutionTable] = useState<JobExecutionTable | null>(null);
  const route = useRoute('akeneo_job_index_action');
  const isMounted = useIsMounted();

  useEffect(() => {
    const searchJobExecution = async () => {
      const response = await fetch(route, {
        body: JSON.stringify({
          page: page.toString(),
          size: size.toString(),
          status,
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
  }, [route, page, size, status]);

  return jobExecutionTable;
};

export {useJobExecutionTable};
