import {useEffect, useState} from 'react';
import {useRoute, useIsMounted} from '@akeneo-pim-community/shared';

const useJobExecutionUsers = (): string[] | null => {
  const [jobExecutionUsers, setJobExecutionUsers] = useState<string[] | null>(null);
  const route = useRoute('akeneo_job_get_job_user_action');
  const isMounted = useIsMounted();

  useEffect(() => {
    const fetchJobExecutionUsers = async () => {
      const response = await fetch(route, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
        },
      });

      if (isMounted()) {
        setJobExecutionUsers(await response.json());
      }
    };

    fetchJobExecutionUsers();
  }, [route, isMounted]);

  return jobExecutionUsers;
};

export {useJobExecutionUsers};
