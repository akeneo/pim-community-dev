import {useEffect, useState} from 'react';
import {useRoute} from '@akeneo-pim-community/shared';

const useUsers = () => {
  const route = useRoute('pimee_job_automation_get_users');
  const [users, setUsers] = useState<string[]>([]);

  useEffect(() => {
    const fetchUsers = async () => {
      const response = await fetch(route, {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      });

      const data = await response.json();

      setUsers(response.ok ? data : {});
    };

    void fetchUsers();
  }, [route]);

  return users;
};

export {useUsers};
