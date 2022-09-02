import {useEffect, useState} from 'react';
import {useRouter} from '@akeneo-pim-community/shared';
import {User} from '../models/User';

const useUsers = () => {
  const router = useRouter();
  const [availableUsers, setAvailableUsers] = useState<User[]>([]);

  useEffect(() => {
    const fetchUsers = async () => {
      // TODO RAB-1018 : Remove limit once we have a MultiSelectInputAsync component with proper pagination
      const response = await fetch(router.generate('pimee_job_automation_get_users', {limit: 500}), {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      });

      const data = await response.json();

      setAvailableUsers(response.ok ? data : {});
    };

    void fetchUsers();
  }, [router]);

  return {availableUsers};
};

export {useUsers};
