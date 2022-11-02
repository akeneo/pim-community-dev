import {IdentifierGenerator} from '../models';
import {useRouter} from '@akeneo-pim-community/shared';
import {useQuery} from 'react-query';

type Response = {
  data?: IdentifierGenerator[];
  isLoading: boolean;
};

const useGetGenerators = (): Response => {
  const router = useRouter();

  const getGeneratorList = async () => {
    return fetch(router.generate('akeneo_identifier_generator_rest_list'), {
      method: 'GET',
      headers: [['X-Requested-With', 'XMLHttpRequest']],
    }).then(res => {
      if (!res.ok) throw new Error(res.statusText);
      return res.json();
    });
  };

  const {data, isLoading} = useQuery<IdentifierGenerator[], Error, IdentifierGenerator[]>(
    'getGeneratorList',
    getGeneratorList
  );

  return {data, isLoading};
};

export {useGetGenerators};
