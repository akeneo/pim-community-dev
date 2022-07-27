import {useEffect, useState} from 'react';
import {useRoute} from '@akeneo-pim-community/shared';

const useUserGroups = () => {
  const route = useRoute('pimee_job_automation_get_user_groups');
  const [userGroups, setUserGroups] = useState<string[]>([]);

  useEffect(() => {
    const fetchUserGroups = async () => {
      const response = await fetch(route, {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        }
      });

      const data = await response.json();

      setUserGroups(response.ok ? data : {});
    };

    void fetchUserGroups();
  }, [route]);

  return userGroups;
};

export {useUserGroups};
