import {useRoute} from '@akeneo-pim-community/shared';
import {useQuery} from "react-query";

const useJobExecutionTypes = (): string[] => {
  const route = useRoute('akeneo_job_get_job_type_action');
  const fetchJobExecutionTypes = async () => {
    const response = await fetch(route, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
      },
    });

    return await response.json();
  };

  const {data} = useQuery<string[]>('process_tracker_job_execution_type', fetchJobExecutionTypes);

  return data ?? [];
};

export {useJobExecutionTypes};
