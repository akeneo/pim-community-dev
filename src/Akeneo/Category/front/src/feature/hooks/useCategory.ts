import {FetchStatus, useFetch, useRoute} from '@akeneo-pim-community/shared';
import type {EditCategoryForm} from '../models';
import {EnrichCategory} from '../models';

type CategoryResponse = {
  load: () => Promise<void>;
  status: FetchStatus;
  category: EnrichCategory | null;
  error: string | null;
};

const useCategory = (categoryId: number): CategoryResponse => {
  const url = useRoute('pim_enriched_category_rest_get', {
    id: categoryId.toString(),
  });

  const [category, load, status, error] = useFetch<EnrichCategory>(url);

  return {load, category, status, error};
};

export {useCategory};
export type {EditCategoryForm, CategoryResponse};
