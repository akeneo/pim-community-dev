import {useQuery} from 'react-query';
import {IdentifierGenerator, IdentifierGeneratorCode} from '../models';
import {useRouter} from '@akeneo-pim-community/shared';
import {IdentifierGeneratorNotFound} from '../errors/IdentifierGeneratorNotFound';

const useIdentifierGenerator: (code: IdentifierGeneratorCode) => {
  data?: IdentifierGenerator;
  error: Error | null;
  isSuccess: boolean;
} = code => {
  const router = useRouter();

  const getIdentifierGenerator = async () => {
    return fetch(router.generate('akeneo_identifier_generator_rest_get', {code}), {
      method: 'GET',
      headers: [['X-Requested-With', 'XMLHttpRequest']],
    }).then(res => {
      if (!res.ok) {
        if (res.status === 404) {
          throw new IdentifierGeneratorNotFound();
        }
        throw new Error(res.statusText);
      }

      return res.json();
    });
  };

  const {error, data, isSuccess} = useQuery<IdentifierGenerator, Error, IdentifierGenerator>(
    'getIdentifierGenerator',
    getIdentifierGenerator
  );

  return {data, error, isSuccess};
};

export {useIdentifierGenerator};
