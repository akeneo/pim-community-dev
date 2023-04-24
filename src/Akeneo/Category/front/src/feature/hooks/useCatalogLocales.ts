import {useRoute} from '@akeneo-pim-community/shared';
import {useQuery} from 'react-query';
import {apiFetch} from '../tools/apiFetch';
import {useCallback} from 'react';

type CatalogLocale = {
  id: number;
  code: string;
  label: string;
  region: string;
  language: string;
};

const useCatalogLocales = () => {
  const url = useRoute('pim_enrich_locale_rest_index', {});

  const fetchCatalogLocales = useCallback(async () => {
    return await apiFetch<CatalogLocale[]>(url, {});
  }, [url]);

  const {data} = useQuery<CatalogLocale[]>(['get-catalog-locales'], fetchCatalogLocales, {staleTime: 60 * 60 * 1000});
  return data;
};
export {useCatalogLocales};
