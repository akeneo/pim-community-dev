import {IdentifierGenerator} from '../models';
import {InvalidIdentifierGenerator, ServerError} from '../errors';
import {useMutation, useQueryClient} from 'react-query';
import {useRouter} from '@akeneo-pim-community/shared';

// eslint-disable-next-line @typescript-eslint/explicit-module-boundary-types
const useCreateIdentifierGenerator = () => {
  const router = useRouter();
  const queryClient = useQueryClient();

  return useMutation(
    async (generator: IdentifierGenerator) => {
      const response = await fetch(router.generate('akeneo_identifier_generator_rest_create'), {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
        body: JSON.stringify(generator),
      });

      if (response.status === 400) {
        const data = await response.json();
        throw new InvalidIdentifierGenerator(data);
      }

      if (response.status !== 201) {
        throw new ServerError();
      }
    },
    {
      onSuccess: () => queryClient.invalidateQueries('getGeneratorList'),
    }
  );
};

export {useCreateIdentifierGenerator};
