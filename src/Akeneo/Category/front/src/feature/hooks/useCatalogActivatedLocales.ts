import {useRoute} from '@akeneo-pim-community/shared';
import {useCallback} from 'react';
import {apiFetch} from '../tools/apiFetch';
import {useQuery} from 'react-query';

const useCatalogActivatedLocales = () => {
  const url = useRoute('internal_api_category_catalog_activated_locales', {});

  const fetchCatalogLocales = useCallback(async () => {
    return await apiFetch<string[]>(url, {});
  }, [url]);

  const {data} = useQuery<string[]>(['get-catalog-activated-locales'], fetchCatalogLocales, {});
  return data;
};
export {useCatalogActivatedLocales};
