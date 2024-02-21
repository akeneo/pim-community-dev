import {IdentifierGeneratorCode} from '../models';
import {ServerError} from '../errors';
import {useMutation, useQueryClient} from 'react-query';
import {useRouter} from '@akeneo-pim-community/shared';

// eslint-disable-next-line @typescript-eslint/explicit-module-boundary-types
const useDeleteIdentifierGenerator = () => {
  const router = useRouter();
  const queryClient = useQueryClient();

  return useMutation(
    async (code: IdentifierGeneratorCode) => {
      const response = await fetch(router.generate('akeneo_identifier_generator_rest_delete', {code}), {
        method: 'DELETE',
        headers: [['X-Requested-With', 'XMLHttpRequest']],
      });

      if (!response.ok) throw new ServerError();
    },
    {
      onSuccess: () => queryClient.invalidateQueries('getGeneratorList'),
    }
  );
};

export {useDeleteIdentifierGenerator};
