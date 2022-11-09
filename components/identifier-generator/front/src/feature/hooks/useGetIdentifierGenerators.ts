import {IdentifierGenerator} from '../models';
import {useRouter} from '@akeneo-pim-community/shared';
import {useQuery} from 'react-query';
import {ServerError} from '../errors';

// eslint-disable-next-line @typescript-eslint/explicit-module-boundary-types
const useGetIdentifierGenerators = () => {
  const router = useRouter();

  return useQuery<IdentifierGenerator[], Error, IdentifierGenerator[]>('getGeneratorList', async () => {
    const response = await fetch(router.generate('akeneo_identifier_generator_rest_list'), {
      method: 'GET',
      headers: [['X-Requested-With', 'XMLHttpRequest']],
    });
    if (!response.ok) throw new ServerError(response.statusText);

    return await response.json();
  });
};

export {useGetIdentifierGenerators};
