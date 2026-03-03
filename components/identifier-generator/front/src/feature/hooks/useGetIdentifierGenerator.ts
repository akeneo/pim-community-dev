import {useQuery} from 'react-query';
import {IdentifierGenerator, IdentifierGeneratorCode} from '../models';
import {useRouter} from '@akeneo-pim-community/shared';
import {IdentifierGeneratorNotFound, ServerError} from '../errors';

type Response = {data?: IdentifierGenerator; error: Error | null};

const useGetIdentifierGenerator = (code: IdentifierGeneratorCode): Response => {
  const router = useRouter();

  const {data, error} = useQuery<IdentifierGenerator, Error, IdentifierGenerator>(
    ['getIdentifierGenerator', code],
    async () => {
      const response = await fetch(router.generate('akeneo_identifier_generator_rest_get', {code}), {
        method: 'GET',
        headers: [['X-Requested-With', 'XMLHttpRequest']],
      });

      if (!response.ok) {
        if (response.status === 404) throw new IdentifierGeneratorNotFound();
        throw new ServerError();
      }

      return await response.json();
    }
  );

  return {data, error};
};

export {useGetIdentifierGenerator};
