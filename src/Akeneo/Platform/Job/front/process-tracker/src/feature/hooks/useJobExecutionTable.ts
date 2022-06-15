import {useRoute} from '@akeneo-pim-community/shared';
import {JobExecutionFilter, JobExecutionTable} from '../models';
import {useQuery} from "react-query";

const AUTO_REFRESH_FREQUENCY = 5000;

const useJobExecutionTable = ({page, size, sort, type, status, code, user, search}: JobExecutionFilter) => {
  const route = useRoute('akeneo_job_index_action');

  const searchJobExecution = async () => {
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

    return await response.json();
  };

  const {data, refetch} = useQuery<JobExecutionTable>(
    ['process_tracker_job_execution_table', page, size, sort, type, status, code, user, search],
    searchJobExecution,
    {
      refetchInterval: AUTO_REFRESH_FREQUENCY,
      refetchOnWindowFocus: true,
      keepPreviousData: true,
    }
  );

  return [data ?? null, refetch] as const;
};

export {useJobExecutionTable};
