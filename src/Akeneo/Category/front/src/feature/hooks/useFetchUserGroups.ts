import {useRoute} from '@akeneo-pim-community/shared';
import {useCallback} from 'react';
import {useQuery} from 'react-query';
import {ResponseStatus} from '../models/ResponseStatus';

const USER_GROUPS_FETCH_STALE_TIME = 60 * 60 * 1000;

type ResultError = Error | null;

type Result = {
  status: ResponseStatus;
  data: UserGroup[] | undefined;
  error: ResultError;
};

export type UserGroup = {
  id: string;
  label: string;
  isDefault: boolean;
};

export const useFetchUserGroups = (): Result => {
  const url = useRoute('pim_enriched_category_rest_user_group_list');

  const fetchUserGroups = useCallback(async () => {
    const response = await fetch(url);

    if (!response.ok) {
      throw new Error();
    }

    return await response.json();
  }, [url]);

  const options = {
    staleTime: USER_GROUPS_FETCH_STALE_TIME,
  };

  return useQuery<UserGroup[], ResultError, UserGroup[]>(['user-groups'], fetchUserGroups, options);
};
