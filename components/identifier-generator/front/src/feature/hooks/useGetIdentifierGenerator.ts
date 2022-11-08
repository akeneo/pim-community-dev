import {useQuery} from 'react-query';
import {IdentifierGenerator, IdentifierGeneratorCode} from '../models';
import {useRouter} from '@akeneo-pim-community/shared';
import {IdentifierGeneratorNotFound, ServerError} from '../errors';

// eslint-disable-next-line @typescript-eslint/explicit-module-boundary-types
const useGetIdentifierGenerator = (code: IdentifierGeneratorCode) => {
  const router = useRouter();

  return useQuery<IdentifierGenerator, Error, IdentifierGenerator>('getIdentifierGenerator', async () => {
    const response = await fetch(router.generate('akeneo_identifier_generator_rest_get', {code}), {
      method: 'GET',
      headers: [['X-Requested-With', 'XMLHttpRequest']],
    });

    if (!response.ok) {
      if (response.status === 404) throw new IdentifierGeneratorNotFound();
      throw new ServerError();
    }

    return await response.json();
  });
};

export {useGetIdentifierGenerator};
