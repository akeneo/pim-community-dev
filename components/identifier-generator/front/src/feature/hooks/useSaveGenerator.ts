import {useMutation, useQueryClient} from 'react-query';
import {NotificationLevel, useNotify, useRouter, useTranslate} from '@akeneo-pim-community/shared';
import {IdentifierGenerator} from '../models';

type HookResponse = {
  isLoading: boolean,
  save: (generator: IdentifierGenerator) => void,
  //error: never
}

const useSaveGenerator = (): HookResponse => {
  const router = useRouter();
  const queryClient = useQueryClient();
  const notify = useNotify();
  const translate = useTranslate();

  const callSave = (generator: IdentifierGenerator) => {
    return fetch(router.generate('akeneo_identifier_generator_rest_update', {code: generator.code}), {
      method: 'PATCH',
      headers: [
        ['Content-type', 'application/json'],
        ['X-Requested-With', 'XMLHttpRequest'],
      ],
      body: JSON.stringify(generator)
    }).then(res => {
      return res.json().then(toto => {
        if (!res.ok) {
          return Promise.reject(toto);
        }
        return toto;
      });
    });
  };

  const {mutate, isLoading, error} = useMutation({mutationFn: callSave});

  const save = (generator: IdentifierGenerator) => mutate(generator, {
    onError: () => {
      notify(NotificationLevel.ERROR, translate('pim_identifier_generator.flash.create.error', {code: generator.code}));
    },
    onSuccess: () => {
      notify(NotificationLevel.SUCCESS, translate('pim_identifier_generator.flash.update.success', {code: generator.code}));
      queryClient.invalidateQueries({ queryKey: ['getIdentifierGenerator'] });
    },
  });

  // eslint-disable-next-line no-console
  console.log({errorInRender: error});

  // @ts-ignore
  return {isLoading, save, error: error || []};
};

export {useSaveGenerator};
