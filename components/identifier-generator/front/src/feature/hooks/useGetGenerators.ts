import {IdentifierGenerator} from '../models';
import {useRouter} from '@akeneo-pim-community/shared';
import {useQuery} from 'react-query';

type Response = {
  data?: IdentifierGenerator[];
  isLoading: boolean;
  error: Error | null;
};

const useGetGenerators = (): Response => {
  const router = useRouter();

  const getGeneratorList = async () => {
    return fetch(router.generate('akeneo_identifier_generator_rest_list'), {
      method: 'GET',
      headers: [['X-Requested-With', 'XMLHttpRequest']],
    }).then(res => {
      return res.json().then(data => {
        if (!res.ok) {
          return Promise.reject(data);
        }
        return data;
      });
    });
  };

  const {data, isLoading, error} = useQuery<IdentifierGenerator[], Error, IdentifierGenerator[]>(
    'getGeneratorList',
    getGeneratorList
  );

  return {data, isLoading, error};
};

export {useGetGenerators};
