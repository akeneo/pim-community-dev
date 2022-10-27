import {useQuery} from 'react-query';
import {FlattenAttribute} from '../models';
import {useRouter} from '@akeneo-pim-community/shared';

const useIdentifierAttributes: () => {
  data?: FlattenAttribute[];
  error: Error | null;
  isSuccess: boolean;
} = () => {
  const router = useRouter();

  const getIdentifierAttributes = async () => {
    return fetch(router.generate('akeneo_identifier_generator_get_identifier_attributes'), {
      method: 'GET',
      headers: [['X-Requested-With', 'XMLHttpRequest']],
    }).then(res => {
      if (!res.ok) throw new Error(res.statusText);
      return res.json();
    });
  };

  const {error, data, isSuccess} = useQuery<FlattenAttribute[], Error, FlattenAttribute[]>(
    'getIdentifierAttributes',
    getIdentifierAttributes
  );

  return {data, error, isSuccess};
};

export {useIdentifierAttributes};
