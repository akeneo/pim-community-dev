import {IdentifierGenerator} from '../models';
import {InvalidIdentifierGenerator, ServerError} from '../errors';
import {useMutation, useQueryClient} from 'react-query';
import {useRouter} from '@akeneo-pim-community/shared';
import {Violation} from '../validators';
import {UseMutateFunction} from 'react-query/types/react/types';

type ErrorResponse = {
  violations?: Violation[];
};

type HookResponse = {
  mutate: UseMutateFunction<IdentifierGenerator, ErrorResponse, IdentifierGenerator, unknown>;
  error: ErrorResponse;
  isLoading: boolean;
};

const useCreateIdentifierGenerator = (): HookResponse => {
  const router = useRouter();
  const queryClient = useQueryClient();

  const {mutate, error, isLoading} = useMutation<IdentifierGenerator, ErrorResponse, IdentifierGenerator>(
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

      return await response.json();
    },
    {
      onSuccess: () => queryClient.invalidateQueries('getGeneratorList'),
    }
  );

  return {mutate, error: error ?? {}, isLoading};
};

export {useCreateIdentifierGenerator};
