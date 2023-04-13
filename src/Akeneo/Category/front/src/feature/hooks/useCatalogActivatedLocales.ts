import {FetchStatus, useFetch, useRoute} from '@akeneo-pim-community/shared';

type CatalogActivatedLocalesResponse = {
  load: () => Promise<void>;
  status: FetchStatus;
  localeCodes: string[] | null;
  error: string | null;
};

const useCatalogActivatedLocales = (): CatalogActivatedLocalesResponse => {
  const url = useRoute('internal_api_category_catalog_activated_locales', {});
  const [localeCodes, load, status, error] = useFetch<string[]>(url);
  return {load, localeCodes, status, error};
};

export {useCatalogActivatedLocales};
export type {CatalogActivatedLocalesResponse};
