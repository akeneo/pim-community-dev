import {useEffect, useState} from 'react';
import {JobExecutionTable} from '../models/JobExecutionTable';
import {useRoute, useIsMounted} from '@akeneo-pim-community/shared';

const useJobExecutionTable = (): JobExecutionTable | null => {
  const [jobExecutionTable, setJobExecutionTable] =
    useState<JobExecutionTable | null>(null);
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
        setJobExecutionTable(await response.json());
      }
    };

    searchJobExecution();
  }, [route, isMounted, setJobExecutionTable]);

  return jobExecutionTable;
};

export {useJobExecutionTable};
