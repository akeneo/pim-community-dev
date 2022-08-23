import {useEffect, useState, useCallback} from 'react';
import {useRoute} from '@akeneo-pim-community/shared';
import {UserGroup} from "../models/UserGroup";

const useUserGroups = () => {
  const route = useRoute('pimee_job_automation_get_user_groups');
  const [availableUserGroups, setAvailableUserGroups] = useState<UserGroup[]>([]);

  const loadNextPage = useCallback(
      async () => {

        let url = route;
        const searchAfterId = availableUserGroups[availableUserGroups.length-1].id;

        if (searchAfterId) {
          url += `?search_after_id=${searchAfterId}`;
        }

        const response = await fetch(url, {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
          },
        });

        const data = await response.json();

        if (availableUserGroups.length !== 0) {
          setAvailableUserGroups(response.ok ? [...availableUserGroups, ...data] : availableUserGroups);
        }
      },
      [availableUserGroups],
  );

  useEffect(() => {
    const fetchUserGroups = async (searchAfterId: number|null) => {

      const response = await fetch(route, {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      });

      const data = await response.json();

      setAvailableUserGroups(response.ok ? data : []);
    };

    void fetchUserGroups(null);
  }, []);

  return {availableUserGroups, loadNextPage};
};

export {useUserGroups};
