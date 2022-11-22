import {useContext, useMemo} from 'react';
import {FetchStatus, useFetch, useRoute} from '@akeneo-pim-community/shared';
import type {EditCategoryForm} from '../models';
import {EnrichCategory, Template} from '../models';
import {populateCategory} from '../helpers';
import {EditCategoryContext} from '../components';
import {useTemplateByTemplateUuidInMemory} from './useTemplateByTemplateUuidInMemory';

interface UseCategoryResponseCommon {
  load: () => Promise<void>;
  status: FetchStatus;
}
export interface UseCategoryResponseOK extends UseCategoryResponseCommon {
  category: EnrichCategory;
  template: Template;
  status: 'fetched';
}

export interface UseCategoryResponsePending extends UseCategoryResponseCommon {
  status: 'idle' | 'fetching';
}

export interface UseCategoryResponseKO extends UseCategoryResponseCommon {
  status: 'error';
  error: string | Error;
}

export type UseCategoryResponse = UseCategoryResponsePending | UseCategoryResponseOK | UseCategoryResponseKO;

const useCategory = (categoryId: number): UseCategoryResponse => {
  const {channels, locales} = useContext(EditCategoryContext);

  const localeCodes = useMemo(() => Object.keys(locales), [locales]);
  const channelCodes = useMemo(() => Object.keys(channels), [channels]);

  const url = useRoute('pim_enriched_category_rest_get', {
    id: categoryId.toString(),
  });

  const [category, load, categoryFetchingStatus, categoryFetchingError] = useFetch<any>(url);

  const {
    data: template,
    status: templateFetchingStatus,
    error: templateFetchingError,
  } = useTemplateByTemplateUuidInMemory({
    // TODO when available : use template uuid from category.template_id
    uuid: '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
    enabled: categoryFetchingStatus === 'fetched',
  });

  const populatedCategory = useMemo(() => {
    return category && template ? populateCategory(category, template, channelCodes, localeCodes) : null;
  }, [category, template, channelCodes, localeCodes]);

  if (categoryFetchingStatus === 'error') {
    return {load, status: 'error', error: categoryFetchingError!};
  }
  if (templateFetchingStatus === 'error') {
    return {load, status: 'error', error: templateFetchingError!};
  }

  if (categoryFetchingStatus === 'fetched') {
    if (templateFetchingStatus === 'success') {
      return {load, status: 'fetched', category: populatedCategory!, template: template!};
    }
    return {load, status: templateFetchingStatus === 'loading' ? 'fetching' : 'idle'};
  }

  return {load, status: categoryFetchingStatus};
};

export {useCategory};
export type {EditCategoryForm};
