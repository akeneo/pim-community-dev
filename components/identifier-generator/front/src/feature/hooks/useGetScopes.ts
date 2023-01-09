import {useQuery} from 'react-query';
import {Channel, useRouter} from '@akeneo-pim-community/shared';
import {ServerError} from '../errors';

const useGetScopes = (): {data?: Channel[]; isLoading: boolean; error: Error | null} => {
  const router = useRouter();

  const {data, isLoading, error} = useQuery<Channel[], Error, Channel[]>({
    queryKey: 'getScopes',
    queryFn: async () => {
      const response = await fetch(router.generate('pim_enrich_channel_rest_index'), {
        method: 'GET',
        headers: [['X-Requested-With', 'XMLHttpRequest']],
      });

      if (!response.ok) {
        throw new ServerError();
      }

      return await response.json();
    },
  });

  return {data, isLoading, error};
};

export {useGetScopes};
