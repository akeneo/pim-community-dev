import {Nomenclature, Operator} from '../models';
import {useQuery} from 'react-query';
import {ServerError} from '../errors';
import {useRouter} from '@akeneo-pim-community/shared';

type HookResponse = {
  data?: Nomenclature,
  error: ServerError| null,
  isLoading: boolean
}

const useGetNomenclature = (propertyCode: string): HookResponse => {
  const router = useRouter();

  const {data, error, isLoading} = useQuery<Nomenclature, ServerError, Nomenclature>({
    queryKey: ['getNomenclature', propertyCode],
    queryFn: async () => {
      const response = await fetch(router.generate('akeneo_identifier_generator_nomenclature_rest_get', {propertyCode}), {
        method: 'GET',
        headers: [['X-Requested-With', 'XMLHttpRequest']],
      });

      if (!response.ok) throw new ServerError(response.statusText);

      return await response.json();
    }
  });

  return {data, error, isLoading};
};

export {useGetNomenclature};
