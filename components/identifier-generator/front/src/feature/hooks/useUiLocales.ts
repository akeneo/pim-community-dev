import {useQuery} from 'react-query';
import {UiLocale} from '../models';
import {useRouter} from '@akeneo-pim-community/shared';
import {ServerError} from '../errors';

// eslint-disable-next-line @typescript-eslint/explicit-module-boundary-types
const useUiLocales = () => {
  const router = useRouter();

  return useQuery<UiLocale[], Error, UiLocale[]>('getUiLocales', async () => {
    const response = await fetch(router.generate('pim_localization_locale_index'), {
      method: 'GET',
      headers: [['X-Requested-With', 'XMLHttpRequest']],
    });
    if (!response.ok) throw new ServerError();

    return await response.json();
  });
};

export {useUiLocales};
