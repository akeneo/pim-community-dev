import {useQuery} from "react-query";
import {useRoute} from '@akeneo-pim-community/shared';

type Data = string[];

const useJobExecutionUsers = () => {
  const route = useRoute('akeneo_job_get_job_user_action');
  const fetchJobExecutionUsers = async () => {
    const response = await fetch(route, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
      },
    });

    return await response.json();
  };

  const {data} = useQuery<Data>('process_tracker_users', fetchJobExecutionUsers);

  return data ?? [];
};

export {useJobExecutionUsers};
