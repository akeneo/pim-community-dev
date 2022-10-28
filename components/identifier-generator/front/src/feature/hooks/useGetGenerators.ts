import {IdentifierGenerator} from '../models';
import {useRouter} from '@akeneo-pim-community/shared';
import {useQuery} from 'react-query';

type Response = {
  data: IdentifierGenerator[];
  isLoading: boolean;
  refetch: () => void;
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

  const {data, isLoading, refetch} = useQuery('getGeneratorList', getGeneratorList);

  return {data, isLoading, refetch};
};

export {useGetGenerators};
