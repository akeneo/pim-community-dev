import {useMutation} from 'react-query';
import {useRouter} from '@akeneo-pim-community/shared';
import {IdentifierGenerator} from '../models';
import {Violation} from '../validators';
import {UseMutateFunction} from 'react-query/types/react/types';

type HookResponse = {
  isLoading: boolean;
  save: UseMutateFunction<IdentifierGenerator, Violation[], IdentifierGenerator>;
  error: Violation[];
};

const sanitizeValues = (generator: IdentifierGenerator) => {
  const sanitizedStructure = generator.structure.map(property => {
    const newObject = {...property};
    if ('locale' in newObject && newObject.locale === null) delete newObject.locale;
    if ('scope' in newObject && newObject.scope === null) delete newObject.scope;
    return newObject;
  });
  return {...generator, structure: sanitizedStructure};
};

const useSaveGenerator = (): HookResponse => {
  const router = useRouter();

  const callSave = async (generator: IdentifierGenerator) => {
    const sanitizedGenerator = sanitizeValues(generator);
    const res = await fetch(router.generate('akeneo_identifier_generator_rest_update', {code: generator.code}), {
      method: 'PATCH',
      headers: [
        ['Content-type', 'application/json'],
        ['X-Requested-With', 'XMLHttpRequest'],
      ],
      body: JSON.stringify(sanitizedGenerator),
    });
    const data = await res.json();

    return res.ok ? data : Promise.reject(data);
  };

  const {mutate, isLoading, error} = useMutation<IdentifierGenerator, Violation[], IdentifierGenerator>(callSave);

  return {isLoading, save: mutate, error: error ?? []};
};

export {useSaveGenerator};
