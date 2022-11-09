import {useContext, useMemo} from 'react';
import {FetchStatus, useFetch, useRoute} from '@akeneo-pim-community/shared';
import type {EditCategoryForm} from '../models';
import {EnrichCategory, Template} from '../models';
import {populateCategory} from '../helpers';
import {EditCategoryContext} from '../components';
import {useTemplateByTemplateUuid} from './useTemplateByTemplateUuid';

interface UseCategoryResponseCommon {
  load: () => Promise<void>;
  categoryStatus: FetchStatus;
  templateStatus: FetchStatus;
}
export interface UseCategoryResponseOK extends UseCategoryResponseCommon {
  category: EnrichCategory;
  template: Template;
  categoryStatus: 'fetched';
  templateStatus: 'fetched';
}

export interface UseCategoryResponsePending extends UseCategoryResponseCommon {
  categoryStatus: 'idle' | 'fetching';
}

export interface UseTemplateResponsePending extends UseCategoryResponseCommon {
  category: EnrichCategory;
  categoryStatus: 'fetched';
  templateStatus: 'idle' | 'fetching';
}

export interface UseCategoryResponseKO extends UseCategoryResponseCommon {
  categoryStatus: 'error';
  error: string | Error;
}

export type UseCategoryResponse = UseCategoryResponsePending | UseCategoryResponseOK | UseCategoryResponseKO | UseTemplateResponsePending;

const useCategory = (categoryId: number): UseCategoryResponse => {
  const {locales} = useContext(EditCategoryContext);

  const localeCodes = useMemo(() => Object.keys(locales), [locales]);

  const url = useRoute('pim_enriched_category_rest_get', {
    id: categoryId.toString(),
  });

  const [category, load, categoryFetchingStatus, categoryFetchingError] = useFetch<any>(url);

  const {
    data: template,
    status: templateFetchingStatus,
    error: templateFetchingError,
  } = useTemplateByTemplateUuid(category?.template_uuid ?? null);

  const populatedCategory = useMemo(() => {
    return category ? populateCategory(category, template, localeCodes) : null;
  }, [category, template, localeCodes]);

  if (categoryFetchingStatus === 'error') {
    return {load, categoryStatus: 'error', templateStatus: 'error', error: categoryFetchingError!};
  }
  if (templateFetchingStatus === 'error') {
    return {load, categoryStatus: 'error', templateStatus: 'error', error: templateFetchingError!};
  }

  if (categoryFetchingStatus === 'fetched') {
    if (templateFetchingStatus === 'success') {
      return {load, categoryStatus: 'fetched', templateStatus: 'fetched', category: populatedCategory!, template: template!};
    }
    return {load, categoryStatus: 'fetched', category: populatedCategory!, templateStatus: (templateFetchingStatus === 'loading') ? 'fetching' : 'idle'};
  }

  return {load, categoryStatus: categoryFetchingStatus, templateStatus: 'fetching'};
};

export {useCategory};
export type {EditCategoryForm};
