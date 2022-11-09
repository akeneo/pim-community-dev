import {useQuery} from 'react-query';
import {FlattenAttribute} from '../models';
import {useRouter} from '@akeneo-pim-community/shared';
import {ServerError} from '../errors';

// eslint-disable-next-line @typescript-eslint/explicit-module-boundary-types
const useIdentifierAttributes = () => {
  const router = useRouter();

  return useQuery<FlattenAttribute[], Error, FlattenAttribute[]>('getIdentifierAttributes', async () => {
    const response = await fetch(router.generate('akeneo_identifier_generator_get_identifier_attributes'), {
      method: 'GET',
      headers: [['X-Requested-With', 'XMLHttpRequest']],
    });
    if (!response.ok) throw new ServerError(response.statusText);

    return await response.json();
  });
};

export {useIdentifierAttributes};
