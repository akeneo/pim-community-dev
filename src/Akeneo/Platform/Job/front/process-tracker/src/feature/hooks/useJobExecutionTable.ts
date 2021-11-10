import {useEffect, useState} from 'react';
import {JobExecutionTable} from '../models/JobExecutionTable';
import {useRoute, useIsMounted} from '@akeneo-pim-community/shared';

const useJobExecutionTable = (page: number, size: number, type: string[]): JobExecutionTable | null => {
  const [jobExecutionTable, setJobExecutionTable] = useState<JobExecutionTable | null>(null);
  const route = useRoute('akeneo_job_index_action', {
    page: page.toString(),
    size: size.toString(),
    type: JSON.stringify(type),
  });
  const isMounted = useIsMounted();

  useEffect(() => {
    const searchJobExecution = async () => {
      const response = await fetch(route, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
        },
      });

      if (isMounted()) {
        setJobExecutionTable(await response.json());
      }
    };

    searchJobExecution();
  }, [type, route, isMounted, setJobExecutionTable]);

  return jobExecutionTable;
};

export {useJobExecutionTable};
