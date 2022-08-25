import {useCallback, useEffect, useState} from 'react';
import {useRouter} from '@akeneo-pim-community/shared';
import {User} from '../models/User';

const useUsers = () => {
  const router = useRouter();
  const [availableUsers, setAvailableUsers] = useState<User[]>([]);

  const search = useCallback(
    async (search: string) => {
      const response = await fetch(router.generate('pimee_job_automation_get_users', {search: search}), {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      });

      const data = await response.json();

      setAvailableUsers(response.ok ? data : {});
    },
    [router]
  );

  const loadNextPage = useCallback(async () => {
    const searchAfterId = availableUsers.length > 0 ? availableUsers[availableUsers.length - 1].id : null;

    const response = await fetch(router.generate('pimee_job_automation_get_users', {search_after_id: searchAfterId}), {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    });

    const data = await response.json();

    setAvailableUsers(response.ok ? [...availableUsers, ...data] : availableUsers);
  }, [availableUsers, router]);

  useEffect(() => {
    const fetchUsers = async () => {
      const response = await fetch(router.generate('pimee_job_automation_get_users'), {
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

  return {availableUsers, loadNextPage, search};
};

export {useUsers};
