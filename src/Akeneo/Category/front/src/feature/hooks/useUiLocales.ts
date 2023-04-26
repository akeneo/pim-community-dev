import {useRoute} from '@akeneo-pim-community/shared';
import {useQuery} from 'react-query';
import {apiFetch} from '../tools/apiFetch';
import {useCallback} from 'react';

type UiLocale = {
  id: number;
  code: string;
  label: string;
  region: string;
  language: string;
};

const useUiLocales = () => {
  const url = useRoute('pim_localization_locale_index', {});

  const fetchUiLocales = useCallback(async () => {
    return await apiFetch<UiLocale[]>(url, {});
  }, [url]);

  const options = {
    staleTime: 60 * 60 * 1000,
  };

  const {data} = useQuery<UiLocale[]>(['get-ui-locales'], fetchUiLocales, options);
  return data;
};
export {useUiLocales};
