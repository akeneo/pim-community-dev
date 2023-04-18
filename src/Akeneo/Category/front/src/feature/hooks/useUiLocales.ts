import {UiLocale} from '../models';
import {useRoute} from '@akeneo-pim-community/shared';
import {useQuery} from 'react-query';
import {apiFetch} from '../tools/apiFetch';
import {useCallback} from 'react';
import {ResponseStatus} from '../models/ResponseStatus';

const UI_LOCALES_FETCH_STALE_TIME = 60 * 60 * 1000;

type ResultError = Error | null;

type Result = {
  status: ResponseStatus;
  data: UiLocale[] | undefined;
  error: ResultError;
};

const useUiLocales = (): Result => {
  const url = useRoute('pim_localization_locale_index', {});

  const fetchUiLocales = useCallback(async () => {
    return await apiFetch<UiLocale[]>(url, {});
  }, [url]);

  const options = {
    staleTime: UI_LOCALES_FETCH_STALE_TIME,
  };

  return useQuery<UiLocale[], ResultError, UiLocale[]>(['get-ui-locales'], fetchUiLocales, options);
};
export {useUiLocales};
