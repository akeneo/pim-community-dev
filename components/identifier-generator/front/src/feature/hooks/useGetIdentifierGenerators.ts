import {IdentifierGenerator} from '../models';
import {useRouter} from '@akeneo-pim-community/shared';
import {useQuery} from 'react-query';
import {ServerError} from '../errors';

type HookResponse = {
  data?: IdentifierGenerator[];
  isLoading: boolean;
  error: ServerError | null;
};

const useGetIdentifierGenerators = (): HookResponse => {
  const router = useRouter();

  const {data, isLoading, error} = useQuery<IdentifierGenerator[], ServerError, IdentifierGenerator[]>(
    'getGeneratorList',
    async () => {
      const response = await fetch(router.generate('akeneo_identifier_generator_rest_list'), {
        method: 'GET',
        headers: [['X-Requested-With', 'XMLHttpRequest']],
      });
      if (!response.ok) throw new ServerError(response.statusText);

      return await response.json();
    }
  );

  return {data, isLoading, error};
};

export {useGetIdentifierGenerators};
