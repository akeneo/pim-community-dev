import {useCallback, useEffect, useState} from 'react';
import {useRoute} from '@akeneo-pim-community/shared';
import {User} from '../models/User';

const useUsers = () => {
  const route = useRoute('pimee_job_automation_get_users');
  const [availableUsers, setAvailableUsers] = useState<User[]>([]);

  const loadNextPage = useCallback(async () => {
    let url = route;
    const searchAfterId = availableUsers[availableUsers.length - 1].id;

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

    if (availableUsers.length !== 0) {
      setAvailableUsers(response.ok ? [...availableUsers, ...data] : availableUsers);
    }
  }, [availableUsers]);

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

      setAvailableUsers(response.ok ? data : {});
    };

    void fetchUsers();
  }, [route]);

  return {availableUsers, loadNextPage};
};

export {useUsers};
