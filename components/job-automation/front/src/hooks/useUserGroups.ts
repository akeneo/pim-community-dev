import {useEffect, useState} from 'react';
import {UserGroup} from '../models/UserGroup';
import {useRouter} from '@akeneo-pim-community/shared';

const useUserGroups = () => {
  const router = useRouter();
  const [availableUserGroups, setAvailableUserGroups] = useState<UserGroup[]>([]);

  useEffect(() => {
    const fetchUserGroups = async () => {
      // TODO RAB-1018 : Remove limit once we have a MultiSelectInputAsync component with proper pagination
      const response = await fetch(router.generate('pimee_job_automation_get_user_groups', {limit: 10000}), {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      });

      const data = await response.json();

      setAvailableUserGroups(response.ok ? data : []);
    };

    void fetchUserGroups();
  }, [router]);

  return {availableUserGroups};
};

export {useUserGroups};
