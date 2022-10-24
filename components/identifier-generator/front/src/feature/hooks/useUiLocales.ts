import {useQuery} from 'react-query';
import {UiLocale} from '../models';
import {useRouter} from '@akeneo-pim-community/shared';

const useUiLocales: () => {
  data?: UiLocale[];
  error: Error | null;
  isSuccess: boolean;
} = () => {
  const router = useRouter();

  const getUiLocales = async () => {
    return fetch(router.generate('pim_localization_locale_index'), {
      method: 'GET',
      headers: [['X-Requested-With', 'XMLHttpRequest']],
    }).then(res => {
      if (!res.ok) throw new Error(res.statusText);
      return res.json();
    });
  };

  const {error, data, isSuccess} = useQuery<UiLocale[], Error, UiLocale[]>('getUiLocales', getUiLocales, {
    keepPreviousData: true,
    refetchOnWindowFocus: false,
    retry: false,
  });

  return {data, error, isSuccess};
};

export {useUiLocales};
