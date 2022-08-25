import {useEffect, useState, useCallback} from 'react';
import {UserGroup} from '../models/UserGroup';
import {useRouter} from '@akeneo-pim-community/shared';

const useUserGroups = () => {
  const router = useRouter();
  const [availableUserGroups, setAvailableUserGroups] = useState<UserGroup[]>([]);

  const searchName = useCallback(
    async (name: string) => {
      const response = await fetch(router.generate('pimee_job_automation_get_user_groups', {search_name: name}), {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      });

      const data = await response.json();

      setAvailableUserGroups(response.ok ? data : {});
    },
    [router]
  );

  const loadNextPage = useCallback(async () => {
    const searchAfterId =
      availableUserGroups.length > 0 ? availableUserGroups[availableUserGroups.length - 1].id : null;

    const response = await fetch(
      router.generate('pimee_job_automation_get_user_groups', {search_after_id: searchAfterId}),
      {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      }
    );

    const data = await response.json();

    setAvailableUserGroups(response.ok ? [...availableUserGroups, ...data] : availableUserGroups);
  }, [availableUserGroups, router]);

  useEffect(() => {
    const fetchUserGroups = async (searchAfterId: number | null) => {
      const response = await fetch(router.generate('pimee_job_automation_get_user_groups'), {
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
  }, [router]);

  return {availableUserGroups, loadNextPage, searchName};
};

export {useUserGroups};
