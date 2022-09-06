import {useMemo} from 'react';
import {FetchStatus, useFetch, useRoute} from '@akeneo-pim-community/shared';
import {EnrichCategory} from '../models';
import type {EditCategoryForm} from '../models';
import {normalizeCategory} from '../helpers';

interface UseCategoryResponseCommon {
  load: () => Promise<void>;
  status: FetchStatus;
}
export interface UseCategoryResponseOK extends UseCategoryResponseCommon {
  category: EnrichCategory;
  status: 'fetched';
}

export interface UseCategoryResponsePending extends UseCategoryResponseCommon {
  status: 'idle' | 'fetching';
}

export interface UseCategoryResponseKO extends UseCategoryResponseCommon {
  status: 'error';
  error: string;
}

export type UseCategoryResponse = UseCategoryResponsePending | UseCategoryResponseOK | UseCategoryResponseKO;

const useCategory = (categoryId: number): UseCategoryResponse => {
  const url = useRoute('pim_enriched_category_rest_get', {
    id: categoryId.toString(),
  });

  const [category, load, status, error] = useFetch<any>(url);

  console.log(category);

  const normalizedCategory = useMemo(() => (category ? normalizeCategory(category) : null), [category]);

  switch (status) {
    case 'fetched':
      return {load, status, category: normalizedCategory!};
    case 'error':
      return {load, status, error: error!};
  }

  return {load, status};
};

export {useCategory};
export type {EditCategoryForm};
