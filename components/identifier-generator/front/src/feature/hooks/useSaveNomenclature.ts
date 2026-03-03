import {Nomenclature} from '../models';
import {useMutation, useQueryClient} from 'react-query';
import {useRouter} from '@akeneo-pim-community/shared';
import {UseMutateFunction} from 'react-query/types/react/types';
import {Violation} from '../validators';

const useSaveNomenclature: () => {
  save: UseMutateFunction<void, Violation[], Nomenclature>;
  isLoading: boolean;
} = () => {
  const router = useRouter();
  const queryClient = useQueryClient();

  const {mutate: save, isLoading} = useMutation<void, Violation[], Nomenclature>(
    async (nomenclature: Nomenclature) => {
      const response = await fetch(
        router.generate('akeneo_identifier_generator_nomenclature_rest_update', {
          propertyCode: nomenclature.propertyCode,
        }),
        {
          method: 'PATCH',
          headers: [
            ['Content-type', 'application/json'],
            ['X-Requested-With', 'XMLHttpRequest'],
          ],
          body: JSON.stringify(nomenclature),
        }
      );

      const data = await response.json();

      return response.ok ? data : Promise.reject(data);
    },
    {
      onSuccess: () => queryClient.invalidateQueries('getNomenclature'),
    }
  );

  return {save, isLoading};
};

export {useSaveNomenclature};
