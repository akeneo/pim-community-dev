import {useRoute} from '@akeneo-pim-community/shared';
import {useCallback} from 'react';
import {useQuery} from 'react-query';
import {ResponseStatus} from '../models/ResponseStatus';

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

  return useQuery<UserGroup[], ResultError, UserGroup[]>(['user-groups'], fetchUserGroups);
};
